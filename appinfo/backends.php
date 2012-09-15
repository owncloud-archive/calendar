<?php
OCA\Calendar::registerBackend('database', '\OCA\Calendar\Backend\Database');
//OCA\Calendar::registerBackend('localstorage', '\OCA\Calendar\Backend\LocalStorage');
//OCA\Calendar::registerBackend('share', '\OCA\Calendar\Backend\Share');
OCA\Calendar::registerBackend('webcal', '\OCA\Calendar\Backend\WebCal');
//OCA\Calendar::registerBackend('caldav', ...);
//OCA\Calendar::registerBackend('activesync', ...);