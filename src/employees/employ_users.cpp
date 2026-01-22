/* MIT License

* Copyright (c) 2019-2025 Evgenii Sopov

* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:

* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.

* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

// https://github.com/sea5kg/gtree

#include "employ_users.h"

#include <algorithm>
#include "employ_database.h"
#include "employ_uuids.h"

REGISTRY_WJSCPP_SERVICE_LOCATOR(EmployUsers)

EmployUsers::EmployUsers()
  : WsjcppEmployBase({EmployUsers::name()}, {EmployDatabase::name(), EmployUuids::name()}) {
  TAG = EmployUsers::name();
}

bool EmployUsers::init(const std::string &sName, bool bSilent) {
  auto *pDb = findWsjcppEmploy<EmployDatabase>();
  auto pDbUsers = pDb->dbUsers();
  std::pair<std::string, std::string> res = pDbUsers->findUserByNameAndPass("admin", "admin");
  if (res.first != "") {
    WsjcppLog::warn(TAG, "Found default user 'admin' with default password 'admin' please change password or remove this user.");
  }
  return true;
}

bool EmployUsers::deinit(const std::string &sName, bool bSilent) {
  return true;
}



