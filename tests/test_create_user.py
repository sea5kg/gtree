#!/usr/bin/env python3

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

print("createUser")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session,
}, json={
    "jsonrpc": "2.0",
    "method": "createUser",
    "id": str(uuid.uuid4()),
    "params": {
        "name": "user1",
        "pass": "user2",
        "role": "user",
    },
})

print(resp.status_code)
print(resp.json())