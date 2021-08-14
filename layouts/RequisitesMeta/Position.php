<?php /** @var $positions array */ ?>
<div>

    <input
        type="button"
        class="button"
        value="Добавить должность"
        onclick="window.location.href = '?view=meta-position&add'"
    />
    <br/>
    <br/>
    <?php
    (new \Environment\UI\Table([
        [ 'Name' => 'UITableNumberer', 'Caption' => '#' ],
        [ 'Name' => 'Name',	'Caption' => 'Наименование' ]
    ]))->setNumbererStart(1)
        ->setClass('data')
        ->load($positions)
        ->setOnRecordClick("window.location.href = '?view=meta-position&id={IDRepresentativePosition}'")
        ->write();

    ?>
</div>
