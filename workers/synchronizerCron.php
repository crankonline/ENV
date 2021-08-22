<?php
/**
 * Reregister
 * class for system cron
 */
namespace Environment;

chdir(dirname(__DIR__));

require 'core/configuration.php';

use Environment\DataLayers\Reregister\Core as CoreSchema,
    Environment\Soap\Clients as SoapClients,
    Environment\Vendors\Hal9K\Sync as Sync;

function now()
{
    return date('d.m.Y H:i:s');
}

function log()
{
    $arguments = func_get_args();
    $log = array_shift($arguments);
    $message = now() . ' > ' . call_user_func_array('sprintf', $arguments) . PHP_EOL;

    echo $message;

    fwrite($log, $message);
}

$dlSync = new CoreSchema\Sync();

if(!is_dir(SYSTEM_ROOT . 'logs')) {
    mkdir(SYSTEM_ROOT . 'logs');
}

$log = fopen(PATH_LOGS . basename(__FILE__) . '.log', 'a+');

list($hour, $minute) = explode(':', date('H:i'));

$client = new SoapClients\Requisites\Data();

if (($hour == 23) && ($minute >= 58)) {
    echo now(), ' > Skipping until date changes', PHP_EOL;
} else {
    foreach ($dlSync->getPending() as $uid => $rows) {
        try {
            $requisites = $client->getByUid(API_SUBSCRIBER_TOKEN, $uid, null);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            if ($e instanceof \SoapFault) {
                $code = $e->faultcode;
                $message = $e->faultstring;
            } else {
                $code = $e->getCode();
                $message = $e->getMessage();
            }

            log(
                $log,
                'UID[ %s ] > Exception during requisites obtainment: %s - %s',
                $uid,
                $code,
                $message
            );

            continue;
        }

        if (!$requisites) {
            $isUnpended = $dlSync->unsetPending($rows[0]['uid-id']);

            log(
                $log,
                'UID[ %s ] > Requisites not found, unpending: %s',
                $uid,
                $isUnpended ? 'successful' : 'failed'
            );

            continue;
        }

        foreach ($rows as $row) {
            $trigger = Sync::class . '\\' . $row['sync-trigger-name'];
            $trigger = new $trigger();

            try {
                $callId = $dlSync->registerCall($row);

                $result = $trigger->process($requisites);

                $dlSync->registerCallResult([
                    'sync-trigger-call-id' => $callId,
                    'result' => $result
                ]);

                $result = $result ? 'success' : 'failure';
            } catch (\Exception $e) {
                \Sentry\captureException($e);
                if ($e instanceof \SoapFault) {
                    $code = $e->faultcode;
                    $message = $e->faultstring;
                } else {
                    $code = $e->getCode();
                    $message = $e->getMessage();
                }

                $dlSync->registerCallException([
                    'sync-trigger-call-id' => $callId,
                    'code' => $code,
                    'message' => $message
                ]);

                $result = "exception [ {$code} ] - {$message}";
            }

            log(
                $log,
                'UID[ %s ] > Trigger "%s": %s',
                $uid,
                $row['sync-trigger-name'],
                $result
            );
        }
    }
}


fclose($log);
