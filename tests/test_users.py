#!/usr/bin/env python3

import requests
import uuid

print("doLogin")
resp = requests.post("http://localhost:10555/api/", json={
    "jsonrpc": "2.0",
    "method": "doLogin",
    "id": str(uuid.uuid4()),
    "params": {
        "email": "admin",
        "pass": "admin",
    },
})
print(resp.status_code)
session_admin = ""
if resp.status_code == 200:
    session_admin = resp.json()["result"]["session"]
print(resp.json())

print("session: ", session_admin)

print("createUser")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session_admin,
}, json={
    "jsonrpc": "2.0",
    "method": "createUser",
    "id": str(uuid.uuid4()),
    "params": {
        "email": "some_user",
        "pass": "qwerty",
        "role": "user",
    },
})

print(resp.status_code)
print(resp.json())


print("removeUser")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session_admin,
}, json={
    "jsonrpc": "2.0",
    "method": "removeUser",
    "id": str(uuid.uuid4()),
    "params": {
        "email": "some_user",
    },
})

print(resp.status_code)
print(resp.json())

print("createUser (2)")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session_admin,
}, json={
    "jsonrpc": "2.0",
    "method": "createUser",
    "id": str(uuid.uuid4()),
    "params": {
        "email": "test_user",
        "pass": "qwerty",
        "role": "user",
    },
})

print(resp.status_code)
print(resp.json())

print("resetUserPassword")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session_admin,
}, json={
    "jsonrpc": "2.0",
    "method": "resetUserPassword",
    "id": str(uuid.uuid4()),
    "params": {
        "email": "test_user",
        "pass": "qwerty2",
    },
})

print(resp.status_code)
print(resp.json())


print("removeUser")
resp = requests.post("http://localhost:10555/api/", headers={
    "Authorization": session_admin,
}, json={
    "jsonrpc": "2.0",
    "method": "removeUser",
    "id": str(uuid.uuid4()),
    "params": {
        "email": "test_user",
    },
})