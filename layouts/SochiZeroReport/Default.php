<h2><a href="/index.php?view=service-zero-report">Сервисы -> Автоотправка нулевых отчетов</a></h2>

<form class="form" id="ajax_form" method="POST" action="/index.php?view=sochi-zero-report-admin&action=send" >
    <input type="submit" value="Отправить нулевые отчеты">

</form>

<?php if (isset($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <div class="failure"><?php echo $error; ?></div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($response)): ?>
        <div class="message"><?php echo $response; ?></div>
<?php endif; ?>


<form class="form" action="/index.php?view=sochi-zero-report-admin&action=send" method="POST">
    <div class="caption">Отправить нулевые отчеты</div>

    <div class="field">
        <label for="module-id" class="fixed">Модуль сочи:</label>
        <select name="module-id" id="module-id">
            <option>(выбрать)</option>
            <option value="date-nsc">Статком</option>
            <option value="date-sti">Налоговая</option>
            <option value="date-sf">Соцфонд</option>
    </select>
    <span class="hint">Выбрать модуль сочи по отправке нулевых отчетов</span>
    </div>


    <div class="field">
        <label for="day" class="fixed">Число отправки:</label>
        <select name="day" id="day">
            <option>(выбрать)</option>
            <?php
            $date = new DateTime('now');
            $date->modify('last day of this month');
            $day = (int)$date->format('d');
            for ($i=1; $i<= $day; $i++) { ?>
                <option value="date-nsc"><?=$i?></option>
            <?php } ?>
        </select>
        <span class="hint">Выберите число месяца</span>
    </div>


    <div class="field buttons">
        <input type="submit" class="button" value="Отправить" />
    </div>
</form>