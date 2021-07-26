<?php
/** @var $gkeds array */
?>
<link rel="stylesheet" type="text/css" href="resources/css/ui-clients-list.css">
<link rel="stylesheet" type="text/css" href="resources/css/ui-misc-form.css">
<div>

    <input
        type="button"
        class="button"
        value="Добавить ГКЭД"
        onclick="window.location.href = '?view=meta-gked&add'"
    />
    <br/>
    <br/>
<?php
/** @var $ownershipForms array */
/** @var $legalForms array */

(new \Environment\UI\Table([
    [ 'Name' => 'UITableNumberer', 'Caption' => '#' ],
    [ 'Name' => 'Gked',	'Caption' => 'ГКЭД' ],
    [ 'Name' => 'Name',	'Caption' => 'Наименование' ]
]))->setNumbererStart(1)
    ->setClass('data')
    ->load($gkeds)
    ->setOnRecordClick("window.location.href = '?view=meta-gked&id={IDActivity}'")
    ->write();

?>
</div>
