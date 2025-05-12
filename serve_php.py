import subprocess
import time
import os
from flask import Flask, request, Response
import urllib.request
from threading import Thread

app = Flask(__name__)

# Start PHP server in the background
php_server_port = 8000
php_process = None

def start_php_server():
    global php_process
    php_process = subprocess.Popen(['php', '-S', f'localhost:{php_server_port}', '-t', '.'], 
                                  stdout=subprocess.PIPE, 
                                  stderr=subprocess.STDOUT)
    print(f"PHP server started on port {php_server_port}")

# Start PHP server in a separate thread
server_thread = Thread(target=start_php_server)
server_thread.daemon = True
server_thread.start()

# Wait for PHP server to start
time.sleep(2)

@app.route('/', defaults={'path': ''})
@app.route('/<path:path>')
def proxy(path):
    # Build URL to PHP server
    url = f"http://localhost:{php_server_port}/{path}"
    if not path:
        url = f"http://localhost:{php_server_port}/index.php"
    
    # Add query parameters if any
    if request.query_string:
        url += f"?{request.query_string.decode('utf-8')}"
    
    try:
        # Forward headers
        headers = {}
        for header in request.headers:
            if header[0].lower() != 'host':  # Skip the Host header
                headers[header[0]] = header[1]
        
        # Create request to PHP server
        req = urllib.request.Request(url, headers=headers, method=request.method)
        
        # Add body if POST request
        if request.method == 'POST':
            body = request.get_data()
            req.add_header('Content-Length', len(body))
            req.data = body
        
        # Execute request to PHP server
        response = urllib.request.urlopen(req)
        
        # Get response headers
        response_headers = {}
        for header in response.headers.items():
            if header[0].lower() not in ('transfer-encoding', 'connection'):
                response_headers[header[0]] = header[1]
        
        # Return response from PHP server
        return Response(response.read(), status=response.status, headers=response_headers)
    except Exception as e:
        print(f"Error proxying to PHP server: {e}")
        return f"Error: {str(e)}", 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
