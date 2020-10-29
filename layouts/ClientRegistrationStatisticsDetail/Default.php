<form class="form" action="index.php" method="GET">

    <input type="hidden" name="view" value="<?php echo $this::AK_CLIENT_REGISTRATION_STATISTICS; ?>" />
    <div class="caption">Просмотр статистики регистрации клиентов (Детальный)</div>

    <div class="field">
        <label for="period-from" class="fixed">Период с:</label>
        <input type="date" name="period-from" id="period-from" value="<?php echo $periodFrom ? htmlspecialchars($periodFrom) : null; ?>"/>
        <label for="period-to">по:</label>
        <input type="date" name="period-to" id="period-to" value="<?php echo $periodTo ? htmlspecialchars($periodTo) : null; ?>"/>
        <span class="hint">Период осуществления регистрации или корректировок</span>
    </div>

    <div class="field buttons">
        <input type="submit" class="button" value="Просмотр" />
    </div>

</form>

<?php if($errors): ?>
    <?php foreach($errors as $error): ?>
    <div class="failure"><?php echo $error; ?></div>
    <?php endforeach; ?>
    <?php return; ?>
<?php endif; ?>

<?php if(!isset($records)): ?>
    <div class="empty-resultset">Введите данные для поиска.</div>
    <?php return; ?>
<?php elseif(!$records->rowCount()): ?>
    <div class="empty-resultset">Данных не найдено.</div>
    <?php return; ?>
<?php endif; ?>

<table class="data">
    <tr>
        <th>#</th>
        <th>Дата</th>
        <th>IP-адрес</th>
        <th>Оператор</th>
        <th>Реквизиты</th>
    </tr>
    <?php
        $totals = [
            'register-count' => 0,
            'update-count'   => 0
        ];

        foreach($records as $index => $action):
    ?>
    <tr>
        <td class="center"><?php
            echo $index + 1;
        ?></td>
        <td class="center"><?php
            echo htmlspecialchars($action['date']);
        ?></td>
        <td class="center"><?php
            echo htmlspecialchars($action['ip-address']);
        ?></td>
        <td class="center"><?php
            echo htmlspecialchars($t->getOperatorName($action['userId']));
        ?></td>
<!--        <td class="center"><a href="index.php?view=requisites&inn=02307201910148&uid=&date=2019-12-23 12:51:19.61176"/></td>-->
        <td class="center"><a href="index.php?view=client-registration-statistics-detail&ip=<?php
            echo $_GET['ip'];?>&action=getRequisites&req= <?php echo $action['reqId'];
        ?>"><?php echo $action['reqId']; ?></a></td>
    </tr>
    <?php
            $action['action'] == 1 ? $totals['register-count']++ : $totals['update-count']++;

        endforeach;
    ?>
    <tr>
        <th colspan="3">Итого:</td>
        <td class="center"><b>созданных - <?php
            echo $totals['register-count'];
        ?></b></td>
        <td class="center"><b>обновленных - <?php
            echo $totals['update-count'];
        ?></b></td>
    </tr>
</table>