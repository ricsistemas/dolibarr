<?php
/* Copyright (C) 2009 Regis Houssin	<regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *		\file       htdocs/theme/phones/iphone/theme/default/default.css.php
 *		\brief      Fichier de style CSS du theme Iphone default
 *		\version    $Id: default.css.php,v 1.3 2010/04/12 21:49:40 hregis Exp $
 */

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1'); // We need to use translation files to know direction
if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');

require_once("../../../../../master.inc.php");

// Define css type
header('Content-type: text/css');
// Important: Avoid page request by browser and dynamic build at
// each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

?>

body {
	position: relative;
	margin: 0;
	-webkit-text-size-adjust: none;
	min-height: 416px;
	font-family: helvetica,sans-serif;
	-webkit-background-size:0.438em 100%; 
	background: -webkit-gradient(linear,left top,right top,from(#c5ccd4), color-stop(71%, #c5ccd4), color-stop(72%, #cbd2d8), to(#cbd2d8));
	-webkit-touch-callout: none;
}
.center {
	margin: auto;
	display: block;
	text-align: center!important;
}
img {
	border: 0;
}
a:hover .arrow {
	background-position: 0 -13px!important;
}
@media screen and (max-width: 320px)
{
#topbar {
	height: 44px;
}
#title {
	line-height: 44px;
	height: 44px;
	font-size: 16pt;
}
#tributton a:first-child, #duobutton a:first-child {
	width: 101px;
}
#tributton a:last-child, #duobutton a:last-child {
	width: 101px;
}
#tributton a {
	width: 106px;
}
#duobutton .links {
	width: 195px;
}
#tributton .links {
	width: 302px;
}
#doublead {
	width: 300px!important;
}
#duoselectionbuttons {
	width: 191px;
	height: 30px;
	top: 7px;
}
#triselectionbuttons {
	width: 290px;
	height: 30px;
	top: 7px;
}
#triselectionbuttons a:first-child, #duoselectionbuttons a:first-child {
	width: 99px;
	height: 28px;
	line-height: 28px;
}
#triselectionbuttons a {
	width: 98px;
	height: 28px;
	line-height: 28px;
}
#triselectionbuttons a:last-child, #duoselectionbuttons a:last-child {
	width: 99px;
	height: 28px;
	line-height: 28px;
}
.searchbox form {
	width: 272px;
}
.searchbox input[type="text"] {
	width: 275px;
}
.menu .name {
	max-width: 77%;
}.checkbox .name {
	max-width: 190px;
}.radiobutton .name {
	max-width: 190px;
}
#leftnav a, #rightnav a, #leftbutton a, #rightbutton a, #blueleftbutton a, #bluerightbutton a {
	line-height: 30px;
	height: 30px;
}
#leftnav img, #rightnav img {
	margin-top: 4px;
}
#leftnav, #leftbutton, #blueleftbutton {
	top: 7px;
}
#rightnav, #rightbutton, #bluerightbutton {
	top: 7px;
}
.musiclist .name {
	max-width:55%
}
.textbox textarea {
	width: 280px;
}
.bigfield input{
	width:295px
}
}
@media screen and (min-width: 321px)
{
#topbar {
	height: 32px;
}
#title {
	line-height: 32px;
	height: 32px;
	font-size: 13pt;
}
.menu .name {
	max-width: 85%;
}.checkbox .name {
	max-width: 75%;
}.radiobutton .name {
	max-width: 75%;
}
#leftnav a, #rightnav a, #leftbutton a, #rightbutton a, #blueleftbutton a, #bluerightbutton a {
	line-height: 24px;
	height: 24px;
}
#leftnav img, #rightnav img {
	margin-top: 4px;
	height: 70%;
}
#leftnav, #leftbutton, #blueleftbutton {
	top: 4px;
}
#rightnav, #rightbutton, #bluerightbutton {
	top: 4px;
}
.musiclist .name {
	max-width:70%
}
.textbox textarea {
	width: 440px;
}
#tributton a:first-child, #duobutton a:first-child {
	width: 152px;
}
#tributton a:last-child, #duobutton a:last-child {
	width: 152px;
}
#tributton a {
	width: 154px;
}
#tributton .links {
	width: 452px;
}
#duobutton .links {
	width: 298px;
}
#doublead {
	width: 350px!important;
}
#duoselectionbuttons {
	width: 293px;
	height: 24px;
	top: 4px;
}
#triselectionbuttons {
	width: 450px;
	height: 24px;
	top: 4px;
}
#triselectionbuttons a:first-child, #duoselectionbuttons a:first-child {
	width: 150px;
	height: 22px;
	line-height: 22px;
}
#triselectionbuttons a {
	width: 156px;
	height: 22px;
	line-height: 22px;
}
#triselectionbuttons a:last-child, #duoselectionbuttons a:last-child {
	width: 150px;
	height: 22px;
	line-height: 22px;
}
.searchbox form {
	width: 432px;
}
.searchbox input[type="text"] {
	width: 435px;
}
.bigfield input{
	width:455px
}
}
#topbar.black {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#858585), color-stop(3%, #636363), color-stop(50%, #202020), color-stop(51%, black), color-stop(97%, black), to(#262626));
}
#topbar.transparent {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(133,133,133,0.7)), color-stop(3%, rgba(99,99,99,0.7)), color-stop(50%, rgba(32,32,32,0.7)), color-stop(51%, rgba(0,0,0,0.7)), color-stop(97%, rgba(0,0,0,0.7)), to(rgba(38,38,38,0.7)));
}
#topbar {
	position: relative;
	left: 0;
	top: 0;
	width: auto;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#cdd5df), color-stop(3%, #b0bccd), color-stop(50%, #889bb3), color-stop(51%, #8195af), color-stop(97%, #6d84a2), to(#2d3642));
	margin-bottom: 13px;
}
#title {
	position: absolute;
	font-weight: bold;
	top: 0;
	left: 0;
	right: 0;
	padding: 0 10px;
	text-align: center;
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
	color: #FFF;
	text-shadow: rgba(0,0,0,0.6) 0 -1px 0;
}
#content {
	width: 100%;
	position: relative;
	min-height: 250px;
	margin-top: 10px;
	height: auto;
	z-index: 0;
	overflow: hidden;
}
#footer {
	text-align: center;
	position: relative;
	margin: 20px 10px 0;
	height: auto;
	width: auto;
	bottom: 10px;
}
.ipodlist #footer, .ipodlist #footer a {
	text-shadow: #FFF 0 -1px 0;
}
#footer a, #footer {
	text-decoration: none;
	font-size: 9pt;
	color: #4C4C4C;
	text-shadow: #FFF 0 1px 0;
}
.pageitem {
	-webkit-border-radius: 8px;
	background-color: #fff;
	border: #878787 solid 1px;
	font-size: 12pt;
	overflow: hidden;
	padding: 0;
	position: relative;
	display: block;
	height: auto;
	width: auto;
	margin: 3px 9px 17px;
	list-style: none;
}
.textbox {
	padding: 5px 9px;
	position: relative;
	overflow: hidden;
	border-top: 1px solid #878787;
}
#tributton, #duobutton {
	height: 44px;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#cdd4d9), color-stop(3%, #c0c9cf), color-stop(97%, #abb7bf),to(#81929f));
	margin: -13px 0 13px 0;
	text-align: center;
}
#tributton .links, #duobutton .links {
	height: 30px;
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/tributton.png'; ?>") 0 4 0 4;
	border-width: 0 4px 0 4px;
	margin: 0 auto 0px auto;
	position: relative;
	top: 7px;
}
#tributton a:first-child, #duobutton a:first-child {
	border-right: 1px solid #6d7e91;
	-webkit-border-top-left-radius: 5px;
	-webkit-border-bottom-left-radius: 5px;
	margin-left: -4px;
}
#tributton a, #duobutton a {
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
	height: 27px;
	display: inline-block;
	line-height: 27px;
	margin-top: 1px;
	font: bold 13px;
	text-decoration: none;
	color: #3f5c84;
	text-shadow: #FFF 0 1px 0;
}
#duobutton a:last-child {
	border: 0;
}
#tributton a:last-child {
	border-left: 1px solid #6d7e91;
}
#tributton a:last-child, #duobutton a:last-child {
	-webkit-border-top-right-radius: 5px;
	-webkit-border-bottom-right-radius: 5px;
	margin-right: -4px;
}
#tributton a:hover, #tributton a#pressed, #duobutton a:hover, #duobutton a#pressed {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#7b8b9f), color-stop(3%, #8c9baf), to(#647792));
	color: white;
	text-shadow: black 0 -1px 0;
}
#triselectionbuttons, #duoselectionbuttons {
	-webkit-border-image: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navbutton.png'; ?>') 0 5 0 5;
	border-width: 0 5px 0 5px;
	position: relative;
	margin: auto;
}
#duoselectionbuttons a:first-child {
	border: 0;
}
#triselectionbuttons a:first-child {
	border-right: solid 1px #556984;
}
#triselectionbuttons a:first-child, #duoselectionbuttons a:first-child {
	margin-left: -4px;
	-webkit-border-top-left-radius: 6px;
	-webkit-border-bottom-left-radius: 6px;
}
#triselectionbuttons a, #duoselectionbuttons a {
	display: inline-block;
	text-align: center;
	color: white;
	text-decoration: none;
	margin-top: 1px;
	text-shadow: black 0 -1px 0;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#909baa), color-stop(3%, #a5b4c6), color-stop(50%, #798eaa), color-stop(51%, #6b83a1), color-stop(97%, #6e85a3), to(#526379));
}
#triselectionbuttons a:last-child, #duoselectionbuttons a:last-child {
	border-left: solid 1px #556984;
	margin-right: -4px;
	-webkit-border-top-right-radius: 6px;
	-webkit-border-bottom-right-radius: 6px;
}
#triselectionbuttons a:hover, #triselectionbuttons a#pressed, #duoselectionbuttons a:hover, #duoselectionbuttons a#pressed {
	background: none;
}
#doublead {
	height: 83px!important;
	position: relative;
	margin: 0 auto 13px auto;
}
#doublead a:first-child {
	left: 0!important;
}
#doublead a:last-child {
	right: 0!important;
}
#doublead a {
	width: 147px!important;
	height: 83px!important;
	position: absolute;
	-webkit-border-radius: 8px;
	display: block;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#7c7c7c), color-stop(3%, #858585), color-stop(97%, #a4a4a4),to(#c2c2c2));
}
li#doublead {
	margin-top: 25px;
	margin-bottom: 10px!important;
	background: none;
}
li#doublead:hover {
	background: none;
}
.searchbox {
	height: 44px;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#f1f3f4), color-stop(3%, #e0e4e7), color-stop(50%, #c7cfd4), color-stop(51%, #bec7cd), color-stop(97%, #b4bec6), to(#8999a5));
	margin: -13px 0 13px 0;
	width: 100%;
}
.searchbox form {
	height: 24px;
	-webkit-border-image: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/searchfield.png'; ?>') 4 14 1 24;
	border-width: 4px 14px 1px 24px;
	display: block;
	position: relative;
	top: 8px;
	margin: auto;
}
fieldset {
	border: 0;
	margin: 0;
	padding: 0;
}
.searchbox input[type="text"] {
	border: 0;
	-webkit-appearance: none;
	height: 18px;
	float: left;
	font-size: 13px;
	padding: 0;
	position: relative;
	top: 2px;
	left: 2px;
}
.textbox img {
	max-width: 100%;
}
.textbox p {
	margin-top: 2px;
}
.textbox p {
	margin-top: 2px;
	color: #000;
	margin-bottom: 2px;
	text-align: justify;
}
.textbox img {
	max-width: 100%;
}
.textbox ul {
	margin: 3px 0 3px 0;
	list-style: circle!important;
}
.textbox li {
	margin: 0!important;
}
.pageitem li:first-child, .pageitem li.form:first-child {
	border-top: 0;
}
.menu, .checkbox, .radiobutton, .select, li.button, li.bigfield, li.smallfield {
	position: relative;
	list-style-type: none;
	display: block;
	height: 43px;
	overflow: hidden;
	border-top: 1px solid #878787;
	width: auto;
}
.pageitem li:first-child:hover, .pageitem li:first-child a, .radiobutton:first-child input, .select:first-child select, li.button:first-child input, .bigfield:first-child input {
	-webkit-border-top-left-radius: 8px;
	-webkit-border-top-right-radius: 8px;
}
.pageitem li:last-child:hover, .pageitem li:last-child a, .radiobutton:last-child input, .select:last-child select, li.button:last-child input, .bigfield:last-child input {
	-webkit-border-bottom-left-radius: 8px;
	-webkit-border-bottom-right-radius: 8px;
}
.menu:hover, .store:hover, .list #content li a:hover, .list .withimage:hover, .applist li:hover:nth-child(n),.ipodlist li:hover:nth-child(n) {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#058cf5), to(#015fe6));
}
.ipodlist li:hover:nth-child(n) .name,.ipodlist li:hover:nth-child(n) .time{border:0}
.menu a:hover .name, .store:hover .starcomment, .store:hover .name, .store:hover .comment, .list .withimage a:hover .comment {
	color: #fff;
}
.menu a:hover .comment {
	color: #CCF;
}
.menu a {
	display: block;
	height: 43px;
	width: auto;
	text-decoration: none;
}
.menu a img {
	width: auto;
	height: 32px;
	margin: 5px 0 0 5px;
	float: left;
}
.menu .name, .checkbox .name, .radiobutton .name {
	margin: 11px 0 0 7px;
	width: auto;
	color: #000;
	font-weight: bold;
	font-size: 17px;
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
	float: left;
}
.menu .comment {
	margin: 11px 30px 0 0;
	width: auto;
	font-size: 17px;
	text-overflow: ellipsis;
	overflow: hidden;
	max-width: 75%;
	white-space: nowrap;
	float: right;
	color: #324f85;
}
.menu .arrow, .store .arrow, .musiclist .arrow, .list .arrow {
	position: absolute;
	width: 8px!important;
	height: 13px!important;
	right: 10px;
	top: 15px;
	margin: 0!important;
	background: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/arrow.png'; ?>") 0 0 no-repeat;
}
.applist .arrow {
	position: absolute;
	width: 8px!important;
	height: 13px!important;
	right: 10px;
	top: 29px;
	margin: 0!important;
	background: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/arrow.png'; ?>") 0 0 no-repeat;
}
.store {
	height: 90px;
	border-top: #878787 solid 1px;
	overflow: hidden;
	position: relative;
}
.store a {
	width: 100%;
	height: 90px;
	display: block;
	text-decoration: none;
	position: absolute;
}
.store .image {
	position: absolute;
	left: 0;
	top: 0;
	height: 90px;
	width: 90px;
	display: block;
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#eff1f5), to(#d6dce6));
	-webkit-background-size: 90px;
}
.applist .image {
	width: 57px;
	height: 57px;
	display: block;
	position: absolute;
	top: 7px;
	left: 11px;
	-webkit-border-radius: 8px;
	-webkit-box-shadow: 0 2px 3px rgb(0,0,0);
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#7c7c7c), color-stop(3%, #858585), color-stop(97%, #a4a4a4),to(#c2c2c2));
	-webkit-background-size: 57px;
}
li:first-child.store .image, .store:first-child a {
	-webkit-border-top-left-radius: 8px 8px;
}
li:last-child.store .image, .store:last-child a {
	-webkit-border-bottom-left-radius: 8px 8px;
}
.store .name, .applist .name {
	font-size: 15px;
	white-space: nowrap;
	display: block;
	overflow: hidden;
	color: #000;
	max-width: 60%;
	text-overflow: ellipsis;
	font-weight: bold;
}
.store .name {
	position: absolute;
	left: 95px;
	top: 35px;
}
.applist .name {
	position: absolute;
	top: 27px;
	left: 80px;
	text-shadow: #eeeeee 0 1px 0;
}
.store .comment, .list .withimage .comment, .applist .comment, .applist .price {
	font-size: 12px;
	color: #7f7f7f;
	display: block;
	width: 60%;
	font-weight: bold;
	white-space: nowrap;
	text-overflow: ellipsis;
	overflow: hidden;
}
.store .comment, .list .withimage .comment {
	margin: 16px 0 0 95px;
}
.applist .comment {
	position: absolute;
	top: 9px;
	left: 80px;
	text-shadow: #eeeeee 0 1px 0;
	color: #3b3b3b;
}
.applist .price {
	position: absolute;
	top: 29px;
	right: 26px;
	text-shadow: #eeeeee 0 1px 0;
	text-align: right;
	color: #3b3b3b;
}
.store .arrow, .list .withimage .arrow {
	top: 39px!important;
}
.store .stars0, .store .stars1, .store .stars2, .store .stars3, .store .stars4, .store .stars5 {
	position: absolute;
	top: 56px;
	left: 95px;
	width: 65px;
	height: 18px;
	display: block!important;
}
.store .stars0 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/0starsborder.png'; ?>');
}
.store .stars1 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/1starsborder.png'; ?>');
}
.store .stars2 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/2starsborder.png'; ?>');
}
.store .stars3 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/3starsborder.png'; ?>');
}
.store .stars4 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/4starsborder.png'; ?>');
}
.store .stars5, .applist .stars5 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/5stars.png'; ?>');
}
.applist .stars0, .applist .stars1, .applist .stars2, .applist .stars3, .applist .stars4, .applist .stars5 {
	position: absolute;
	top: 46px;
	left: 79px;
	width: 65px;
	height: 18px;
	display: block!important;
}
.applist .stars0 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/0stars.png'; ?>');
}
.applist .stars1 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/1stars.png'; ?>');
}
.applist .stars2 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/2stars.png'; ?>');
}
.applist .stars3 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/3stars.png'; ?>');
}
.applist .stars4 {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/4stars.png'; ?>');
}
.applist .starcomment {
	left: 147px;
	top: 46px;
	color: #3b3b3b;
}
.starcomment {
	position: absolute;
	left: 165px;
	top: 56px;
	font-size: 12px;
	color: #7f7f7f;
	font-weight: lighter;
}
.applist a:hover .name, .applist a:hover .starcomment, .applist a:hover .comment, .applist a:hover .price {
	color: white;
	text-shadow: none;
}
.graytitle {
	position: relative;
	font-weight: bold;
	font-size: 17px;
	right: 20px;
	left: 9px;
	color: #4C4C4C;
	text-shadow: #FFF 0 1px 0;
	padding: 1px 0 3px 8px;
}
.header {
	display: block;
	font-weight: bold;
	color: rgb(73,102,145);
	font-size: 12pt;
	margin-bottom: 6px;
	line-height: 14pt;
}
.musiclist ul, .ipodlist ul, .applist ul {
	padding: 0;
}
.ipodlist ul {
	margin: 0;
}
.musiclist li:nth-child(odd) {
	background: #dddee0
}
.applist li:nth-child(even) {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#adadb0), color-stop(98%, #adadb0), to(#898a8d))
}
.applist li:nth-child(odd) {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#98989c), color-stop(98%, #98989c), to(#898a8d))

}
.ipodlist li:nth-child(even) {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#414041), color-stop(3%, rgba(45,45,45,0.2)), to(rgba(45,45,45,0.2)))
}
.ipodlist li:nth-child(odd) {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#414041), color-stop(3%, rgba(50,50,50,0.4)), to(rgba(50,50,50,0.4)))
}
.musiclist #content li, .ipodlist #content li, .applist #content li {
	list-style: none;
	width: auto;
	position: relative;
}
.musiclist #content li {
	height: 44px;
	border-bottom: 1px solid #e6e6e6;
}
.applist #content li {
	height: 70px;
	margin-bottom: 1px;
}
.ipodlist #content li {
	height: 42px;
}
.ipodlist #content {
	background: -webkit-gradient(radial, 50% -70, 0, 50% 0, 200, from(#444444), to(rgb(13, 13, 13)));
	top: 16px;
}
.musiclist #content li a, .ipodlist #content li a {
	text-decoration: none;
	color: #000;
	width: 100%!important;
	height: 100%;
	display: block;
}
.applist #content li a {
	text-decoration: none;
	color: #000;
	width: 100%;
	height: 100%;
	display: block;
}
.musiclist .number, .musiclist .name, .musiclist .time {
	display: inline-block;
	height: 44px;
	font-weight: bold;
	font-size: large;
	width: 44px;
	text-align: center;
	line-height: 46px;
}
.musiclist .name {
	margin-left: 0;
	width: auto!important;
	font-size: medium;
	padding-left: 5px;
	border-left: solid 1px #e6e6e6;
}
.musiclist .time {
	color: #848484;
	font-size: medium;
	margin-left: 4px;
	width: auto!important;
	font-weight: normal;
}
.musiclist {
	background-image: none!important;
	background-color: #cbcccf;
}
.ipodlist {
	background-image: none!important;
	background-color: black;
}
.applist {
	background-image: none!important;
	background-color: #98989c;
}
.ipodlist span {
	color: white;
	font-weight: bold;
	font-size: 14px;
}
.musiclist .name {
	text-overflow: ellipsis;
	overflow: hidden;
	white-space: nowrap;
}
.musiclist a:hover .name {
	color: #0380f2;
}
.ipodlist .number {
	width: 23px;
	display: block;
	float: left;
	height: 42px;
	margin-right: 3px;
	text-align: right;
	line-height: 43px;
}
.ipodlist .stop, .ipodlist .auto, .ipodlist .play {
	width: 18px;
	display: block;
	float: left;
	height: 10px;
	text-align: right;
	line-height: 43px;
	margin-top: 16px;
}
.ipodlist .play {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/play.gif'; ?>') no-repeat;
}
.ipodlist a:hover .auto, .ipodlist a:hover .play {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/play.gif'; ?>') no-repeat;
	background-position: 0 -10px;
}
.ipodlist .time {
	width: 48px;
	float: right;
	border-left: solid #414041 1px;
	display: block;
	height: 42px;
	text-align: center;
	line-height: 43px;
}
.ipodlist .name {
	display: block;
	float: left;
	width: inherit;
	height: 42px;
	text-overflow: ellipsis;
	line-height: 42px;
	padding-left: 5px;
	overflow: hidden;
	white-space: nowrap;
	max-width: 62%;
	border-left: solid #414041 1px;
}
.list .title {
	background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#a5b1ba), color-stop(3%, #909faa), color-stop(97%, #b5bfc6), to(#989ea4));
	height: 22px!important;
	width: 100%;
	color: #fff;
	font-weight: bold;
	font-size: 16px;
	text-shadow: gray 0 1px 0;
	line-height: 22px;
	padding-left: 20px;
	border-bottom: none!important;
}
.list ul {
	background-color: #fff;
	width: 100%;
	overflow: hidden;
	padding: 0;
	margin: 0;
}
.list #content li {
	height: 40px;
	border-bottom: 1px solid #e1e1e1;
	list-style: none;
}
.list {
	background-color: #fff;
	background-image: none!important;
}
.list #footer {
	margin-top: 24px!important;
}
.ipodlist #footer {
	margin-top: 48px!important;
}
.list #content li a {
	padding: 9px 0 0 20px;
	font-size: large;
	font-weight: bold;
	position: relative;
	display: block;
	color: #000;
	text-decoration: none;
	height: 32px;
}
.list #content li a .name {
	text-overflow: ellipsis;
	overflow: hidden;
	max-width: 93%;
	white-space: nowrap;
	display: block;
}
.list #content li a:hover {
	color: #fff;
}
.list #content {
	margin-top: -13px!important;
}
.ipodlist #content, .musiclist #content, .applist #content {
	margin-top: -29px!important;
}
.list ul img {
	width: 90px;
	height: 90px;
	position: absolute;
	left: 0;
	top: 0;
}
.list .withimage {
	height: 90px!important;
}
.list .withimage .name {
	margin: 13px 0 0 90px;
	text-overflow: ellipsis;
	overflow: hidden;
	max-width: 63%!important;
	white-space: nowrap;
}
.list .withimage .comment {
	margin: 10px auto auto 90px !important;
	max-width: 63%!important;
}
.list .withimage a, .list .withimage:hover a {
	height: 81px!important;
}
#leftnav, #leftbutton, #blueleftbutton {
	position: absolute;
	font-size: 12px;
	left: 9px;
	font-weight: bold;
}
#leftnav, #leftbutton, #rightnav, #rightbutton, #blueleftbutton, #bluerightbutton {
	z-index: 5000;
}
#leftnav a, #rightnav a, #leftbutton a, #rightbutton a, #blueleftbutton a, #bluerightbutton a {
	display: block;
	color: #fff;
	text-shadow: rgba(0,0,0,0.6) 0 -1px 0;
	text-decoration: none;
}
.black #leftnav a:first-child, .transparent #leftnav a:first-child {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navleftblack.png'; ?>") 0 5 0 13;
}
.black #leftnav a, .transparent #leftnav a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navlinkleftblack.png'; ?>") 0 5 0 13;
}
.black #rightnav a:first-child, .transparent #rightnav a:first-child {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navrightblack.png'; ?>") 0 13 0 5;
}
.black #rightnav a, .transparent #rightnav a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navlinkrightblack.png'; ?>") 0 13 0 5;
}
.black #leftbutton a, .black #rightbutton a, .transparent #leftbutton a, .transparent #rightbutton a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navbuttonblack.png'; ?>") 0 5 0 5;
}
#leftnav a:first-child {
	z-index: 2;
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navleft.png'; ?>") 0 5 0 13;
	border-width: 0 5px 0 13px;
	-webkit-border-top-left-radius: 16px;
	-webkit-border-bottom-left-radius: 16px;
	-webkit-border-top-right-radius: 6px;
	-webkit-border-bottom-right-radius: 6px;
	width: auto;
}
#leftnav a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navlinkleft.png'; ?>") 0 5 0 13;
	z-index: 3;
	margin-left: -4px;
	border-width: 0 5px 0 13px;
	padding-right: 4px;
	-webkit-border-top-left-radius: 16px;
	-webkit-border-bottom-left-radius: 16px;
	-webkit-border-top-right-radius: 6px;
	-webkit-border-bottom-right-radius: 6px;
	float: left;
}
#rightnav, #rightbutton, #bluerightbutton {
	position: absolute;
	font-size: 12px;
	right: 9px;
	font-weight: bold;
}
#rightnav a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navlinkright.png'; ?>") 0 13 0 5;
	z-index: 3;
	margin-right: -4px;
	border-width: 0 13px 0 5px;
	padding-left: 4px;
	-webkit-border-top-left-radius: 6px;
	-webkit-border-bottom-left-radius: 6px;
	float: right;
	-webkit-border-top-right-radius: 16px;
	-webkit-border-bottom-right-radius: 16px;
}
#rightnav a:first-child {
	z-index: 2;
	-webkit-border-top-left-radius: 6px;
	-webkit-border-bottom-left-radius: 6px;
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navright.png'; ?>") 0 13 0 5;
	border-width: 0 13px 0 5px;
	-webkit-border-top-right-radius: 16px;
	-webkit-border-bottom-right-radius: 16px;
}
#leftbutton a, #rightbutton a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navbutton.png'; ?>") 0 5 0 5;
	border-width: 0 5px;
	-webkit-border-radius: 6px;
}
#blueleftbutton a, #bluerightbutton a {
	-webkit-border-image: url("<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/navbuttonblue.png'; ?>") 0 5 0 5;
	border-width: 0 5px;
	-webkit-border-radius: 6px;
}
input[type="checkbox"] {
	width: 94px;
	height: 27px;
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/checkbox.png'; ?>');
	-webkit-appearance: none;
	border: 0;
	float: right;
	margin: 8px 4px 0 0;
}
input[type="checkbox"]:checked {
	background-position: 0 27px;
}
input[type="radio"] {
	-webkit-appearance: none;
	border: 0;
	width: 100%;
	height: 100%;
	z-index: 2;
	position: absolute;
	left: 0;
	margin: 0;
	-webkit-border-radius: 0;
}
input[type="radio"]:checked {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/radiobutton.png'; ?>') no-repeat;
	background-position: right center;
}
.radiobutton .name {
	z-index: 1;
}
select {
	-webkit-appearance: none;
	height: 100%;
	width: 100%;
	border: 0;
}
.select select {
	-webkit-border-radius: 0;
	color: #000;
	font-weight: bold;
	font-size: 17px;
}
.select option {
	max-width: 90%;
}
.select .arrow {
	background: url('<?php echo DOL_URL_ROOT.'/theme/phones/iphone/theme/default/img/arrow.png'; ?>');
	width: 8px;
	height: 13px;
	display: block;
	-webkit-transform: rotate(90deg);
	position: absolute;
	right: 10px;
	top: 18px;
}
.button input {
	width: 100%;
	height: 100%;
	-webkit-appearance: none;
	border: 0;
	-webkit-border-radius: 0;
	font-weight: bold;
	font-size: 17px;
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
	background: none;
}
.textbox textarea {
	padding: 0;
	margin-top: 5px;
	font-size: medium;
}
.bigfield input {
	-webkit-appearance: none;
	border: 0;
	height: 100%;
	padding: 0;
	-webkit-border-radius: 0;
	background: transparent;
	font-weight: bold;
	font-size: 17px;
	padding-left: 5px;
}
.smallfield .name {
	width: 48%;
	position: absolute;
	left: 0;
	font-size: 17px;
	text-overflow: ellipsis;
	white-space: nowrap;
	font-weight: bold;
	line-height: 44px;
	font-size: 17px;
	padding-left: 5px;
	overflow: hidden;
}
.smallfield input {
	width: 50%;
	position: absolute;
	right: 0;
	height: 44px;
	-webkit-appearance: none;
	border: none;
	padding: 0;
	background: transparent;
	-webkit-border-radius: 0;
	font-weight: bold;
	font-size: 17px;
}
.smallfield:first-child input {
	-webkit-border-top-right-radius: 8px;
}
.smallfield:last-child input {
	-webkit-border-bottom-right-radius: 8px;
}
