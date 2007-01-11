<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Eventum - Issue Tracking System                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003, 2004, 2005, 2006, 2007 MySQL AB                  |
// |                                                                      |
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to:                           |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+
// | Authors: João Prado Maia <jpm@mysql.com>                             |
// +----------------------------------------------------------------------+
//
// @(#) $Id: login.php 3189 2007-01-11 21:57:57Z glen $
//
require_once("config.inc.php");
require_once(APP_INC_PATH . "db_access.php");
require_once(APP_INC_PATH . "class.auth.php");
require_once(APP_INC_PATH . "class.user.php");
require_once(APP_INC_PATH . "class.validation.php");

if (Validation::isWhitespace($_POST["email"])) {
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=1");
}
if (Validation::isWhitespace($_POST["passwd"])) {
    Auth::saveLoginAttempt($_POST["email"], 'failure', 'empty password');
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=2&email=" . $_POST["email"]);
}

// check if user exists
if (!Auth::userExists($_POST["email"])) {
    Auth::saveLoginAttempt($_POST["email"], 'failure', 'unknown user');
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=3");
}
// check if the password matches
if (!Auth::isCorrectPassword($_POST["email"], $_POST["passwd"])) {
    Auth::saveLoginAttempt($_POST["email"], 'failure', 'wrong password');
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=3&email=" . $_POST["email"]);
}
// check if this user did already confirm his account
if (Auth::isPendingUser($_POST["email"])) {
    Auth::saveLoginAttempt($_POST["email"], 'failure', 'pending user');
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=9", $is_popup);
}
// check if this user is really an active one
if (!Auth::isActiveUser($_POST["email"])) {
    Auth::saveLoginAttempt($_POST["email"], 'failure', 'inactive user');
    Auth::redirect(APP_RELATIVE_URL . "index.php?err=7", $is_popup);
}

Auth::saveLoginAttempt($_POST["email"], 'success');
// redirect to the initial page
@Auth::createLoginCookie(APP_COOKIE, $_POST["email"]);
Session::init(User::getUserIDByEmail($_POST['email']));
if (!empty($_POST["url"])) {
    $extra = '?url=' . urlencode($_POST["url"]);
} else {
    $extra = '';
}
Auth::redirect(APP_RELATIVE_URL . "select_project.php" . $extra);
