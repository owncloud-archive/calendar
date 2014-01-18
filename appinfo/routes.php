<?php

// OCS API

//TODO: SET: mail notification, waiting for PR #4689 to be accepted

OC_API::register('get',
    '/apps/calendar/api/v1/shares',
    array('\OCA\Calendar\Share\Api', 'getAllShares'),
    'calendar');

OC_API::register('post',
    '/apps/calendar/api/v1/shares',
    array('\OCA\Calendar\Share\Api', 'createShare'),
    'calendar');

OC_API::register('get',
    '/apps/calendar/api/v1/shares/{id}',
    array('\OCA\Calendar\Share\Api', 'getShare'),
    'calendar');

OC_API::register('put',
    '/apps/calendar/api/v1/shares/{id}',
    array('\OCA\Calendar\Share\Api', 'updateShare'),
    'calendar');

OC_API::register('delete',
    '/apps/calendar/api/v1/shares/{id}',
    array('\OCA\Calendar\Share\Api', 'deleteShare'),
    'calendar');
