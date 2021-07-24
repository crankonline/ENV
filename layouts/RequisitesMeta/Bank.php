<div>

    <input
        type="button"
        class="button"
        value="Добавить банк"
        onclick="window.location.href = '?view=meta-bank&add'"
    />
    <br/>
    <br/>
    <?php
    /** @var $banks array */

    (new \Environment\UI\Table([
        [ 'Name' => 'UITableNumberer', 'Caption' => '#' ],
        [ 'Name' => 'IDBank', 'Caption' => 'БИК' ],
        [ 'Name' => 'Name',	'Caption' => 'Наименование' ],
        [ 'Name' => 'Address', 'Caption' => 'Адрес' ],
    ]))->setNumbererStart(1)
        ->setClass('data')
        ->load($banks)
        ->setOnRecordClick("window.location.href = '?view=meta-bank&id={IDBank}'")
        ->write();

    ?>
</div>
