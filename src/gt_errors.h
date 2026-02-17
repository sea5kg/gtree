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

#pragma once

#include <string>

// enum class GTError : unsigned int {
//     ERR_10013_MISSING_FIELD_NEW_PASS = 10013,
//     ERR_10014_MISSING_FIELD_OLD_PASS = 10014,
// };

struct GTreeError {
  GTreeError(
    int code,
    const std::string &msg,
    const std::string &msg_ru,
    const std::string &empty
  ) : code(code), msg(msg), msg_ru(msg_ru), empty(empty) {};
  const int code;
  const std::string msg;
  const std::string msg_ru;
  const std::string empty;
};

static const GTreeError ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS(
    1007,
    "Missing or unexpected type for field 'params'.",
    "Отсутствует или указан неожиданный тип для поля 'params'.",
    ""
);

static const GTreeError ERR_01009_NOT_AUTHORIZED(
    1009,
    "You not authorized.",
    "Вы не авторизованы.",
    ""
);

static const GTreeError ERR_10013_MISSING_FIELD_NEW_PASS(
    10013,
    "Missing field 'new_pass' or wrong type.",
    "Отсутствует поле 'new_pass' или указан неверный тип.",
    ""
);

static const GTreeError ERR_10014_MISSING_FIELD_OLD_PASS(
    10014,
    "Missing field 'old_pass' or wrong type.",
    "Отсутствует поле 'old_pass' или имеет неправильный тип.",
    ""
);
