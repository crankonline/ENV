<?php
/**
 * Reregister
 */
namespace Environment\Vendors\Hal9K\Sync;

use Environment\Soap\Types\Requisites\Data\Export\Data as RequisitesExportData;

abstract class Trigger {
    abstract public function process(RequisitesExportData $requisites);
}
?>