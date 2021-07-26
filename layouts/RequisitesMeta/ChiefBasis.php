<?php /** @var $chiefBasises array */ ?>
<div>

    <input
        type="button"
        class="button"
        value="Добавить основание"
        onclick="window.location.href = '?view=meta-chief-basis&add'"
    />
    <br/>
    <br/>
<?php
(new \Environment\UI\Table([
    [ 'Name' => 'UITableNumberer', 'Caption' => '#' ],
    [ 'Name' => 'Name',	'Caption' => 'Наименование' ]
]))->setNumbererStart(1)
    ->setClass('data')
    ->load($chiefBasises)
    ->setOnRecordClick("window.location.href = '?view=meta-chief-basis&id={IDChiefBasis}'")
    ->write();

?>
</div>