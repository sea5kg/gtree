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

#include "request_handler.h"

static const gtree::ResponseError ERR_01001_ONLY_POST_REQUESTS(
    1001,
    "Only 'POST' requests will be handled.",
    "Обрабатываться будут только запросы типа 'POST'.",
    ""
);

static const gtree::ResponseError ERR_01002_INVALID_INCOMING_JSON(
    1002,
    "Invalid incoming json",
    "Недопустимый входящий JSON.",
    ""
);

static const gtree::ResponseError ERR_01003_EXPECTED_JSON_INPUT(
    1003,
    "Expected json object input.",
    "Ожидаемый ввод: объект JSON.",
    ""
);

static const gtree::ResponseError ERR_01004_MISSING_FIELD_JSONRPC(
    1004,
    "Missing field 'jsonrpc'",
    "Отсутствует поле 'jsonrpc'.",
    ""
);

static const gtree::ResponseError ERR_01005_MISSING_FIELD_METHOD(
    1005,
    "Missing field 'method'",
    "Отсутствует поле 'method'.",
    ""
);

static const gtree::ResponseError ERR_01006_UNKNOWN_METHOD(
    1006,
    "Unknown method.",
    "Неизвестный метод.",
    ""
);

static const gtree::ResponseError ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS(
    1007,
    "Missing or unexpected type for field 'params'.",
    "Отсутствует или указан неожиданный тип для поля 'params'.",
    ""
);

static const gtree::ResponseError ERR_01008_YOU_ALREADY_AUTHORIZED(
    1008,
    "You already authorized.",
    "Вы уже авторизованы..",
    ""
);

static const gtree::ResponseError ERR_01009_NOT_AUTHORIZED(
    1009,
    "You not authorized.",
    "Вы не авторизованы.",
    ""
);

static const gtree::ResponseError ERR_01010_ALLOWED_ONLY_FOR_ADMIN(
    1010,
    "Allowed only for admin.",
    "Доступно только для администраторов.",
    ""
);

static const gtree::ResponseError ERR_10011_COULD_NOT_DID_LOGOUT(
    10011,
    "Could not did logout.",
    "Не удалось выйти из системы.",
    ""
);

static const gtree::ResponseError ERR_10012_MISSING_FIELD_EMAIL(
    10012,
    "Missing field 'email' or wrong type.",
    "Отсутствует поле 'email' или указан неверный тип.",
    ""
);

static const gtree::ResponseError ERR_10013_MISSING_FIELD_NEW_PASS(
    10013,
    "Missing field 'new_pass' or wrong type.",
    "Отсутствует поле 'new_pass' или указан неверный тип.",
    ""
);

static const gtree::ResponseError ERR_10014_MISSING_FIELD_OLD_PASS(
    10014,
    "Missing field 'old_pass' or wrong type.",
    "Отсутствует поле 'old_pass' или имеет неправильный тип.",
    ""
);

static const gtree::ResponseError ERR_10021_COULD_NOT_LOGIN(
    10021,
    "Could not login. Wrong email or password field.",
    "Не удалось войти в систему. Неверный адрес электронной почты или пароль.",
    ""
);

static const gtree::ResponseError ERR_10022_MISSING_FIELD_PASS(
    10022,
    "Missing field 'pass' or wrong type.",
    "Отсутствует поле 'pass' или имеет неправильный тип.",
    ""
);
