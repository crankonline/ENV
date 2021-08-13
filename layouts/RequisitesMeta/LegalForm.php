<link rel="stylesheet" type="text/css" href="resources/css/ui-clients-list.css">
<link rel="stylesheet" type="text/css" href="resources/css/ui-misc-form.css">
<div>

    <input
        type="button"
        class="button"
        value="Добавить организационно-правовую форму"
        onclick="window.location.href = '?view=meta-legal-form&add'"
    />
    <br/>
    <br/>
<?php
/** @var $ownershipForms array */
/** @var $legalForms array */

(new \Environment\UI\Table([
    [ 'Name' => 'UITableNumberer', 'Caption' => '#' ],
    [ 'Name' => 'Name',	'Caption' => 'Полное наименование' ],
    [ 'Name' => 'ShortName',	'Caption' => 'Сокращенное наименование' ],
    [ 'Name' => 'Facet',	'Caption' => 'Фасет' ],
    [
        'Name' => 'Lnk',
        'Caption' => 'Форма собственности',
        'Renderer' => function($data) use ($ownershipForms) {
            return $ownershipForms[$data['OwnershipFormID']];
        }
    ]
]))->setNumbererStart(1)
    ->setClass('data')
    ->load($legalForms)
    ->setOnRecordClick("window.location.href = '?view=meta-legal-form&id={IDLegalForm}'")
    ->write();

?>
</div>
