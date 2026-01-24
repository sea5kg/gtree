

import requests
import uuid

print("doLogin")
resp = requests.post("http://localhost:10555/api/", json={
    "jsonrpc": "2.0",
    "method": "doLogin",
    "id": str(uuid.uuid4()),
    "params": {
        "name": "admin",
        "pass": "admin",
    },
})
print(resp.status_code)
session = ""
if resp.status_code == 200:
    session = resp.json()["result"]["session"]
print(resp.json())

print("session", session)

print("doLogout")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session,
}, json={
    "jsonrpc": "2.0",
    "method": "doLogout",
    "id": str(uuid.uuid4()),
})

print(resp.status_code)
print(resp.json())