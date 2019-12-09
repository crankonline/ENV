<form class="form" action="index.php" method="GET">

    <input type="hidden" name="view" value="<?php echo $this::AK_SF_ARCHIVE; ?>" />
    <div class="caption">Просмотр профиля в службе реквизитов</div>

    <div class="field">
        <label for="inn" class="fixed">ИНН:</label>
        <input type="text" name="inn" id="inn" maxlength="14" placeholder="введите ИНН" value="<?php echo isset($_GET['inn']) ? htmlspecialchars($_GET['inn']) : '' ?>">
        <span class="hint">10 или 14 цифр</span>
    </div>

    <div class="field">
        <label for="uid" class="fixed">UID:</label>
        <input type="text" name="uid" id="uid" maxlength="23" style="width: 350px" placeholder="введите UID" value="<?php echo isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '' ?>">
        <span class="hint">23 цифры</span>
    </div>

    <div class="field buttons">
        <input type="submit" class="button" value="Поиск" />
    </div>

</form>

<?php if($errors): ?>
    <?php foreach($errors as $error): ?>
        <div class="failure"><?php //echo $error; ?></div>
    <?php endforeach; ?>
    <?php //return; ?>
<?php endif; ?>

<?php if(!isset($requisites)): ?>
    <div class="empty-resultset">Введите данные для поиска.</div>
    <?php return; ?>
<?php endif; ?>

<?php if(isset($xml)): ?>
<div style=".boxsizingBorder {
    -webkit-box-sizing: border-box;
       -moz-box-sizing: border-box;
            box-sizing: border-box;
}">
<textarea style="width: 100%; height: 400px"><?php echo $xml ?></textarea>
</div>
<?php endif; ?>

<?php if(isset($sfArchive) ): ?>
<br>
<div class="pki-certificate expired">
    <div class="header">
                <span class="w300 center">
                    <b>UIN:</b>
                </span>
        <span class="w200 center">
                    <b>Дата:</b>
                </span>
        <span class="w75 center">
                    <b>Период:</b>
                </span>
        <span class="w200 center">
                    <b>Статус</b>
                </span>
        <span class="w200 center">
                    <b>Ошибка</b>
                </span>
        <span class="w300">
                    <b>Ошибка куратора:</b>
                </span>
        <span class="w-fit">
                </span>
    </div>
</div>
    <?php
    foreach($sfArchive  as $index => $sfReport):
    ?>
<div class="pki-certificate active">
    <div class="header">
                <span class="w300 center">
                    <?php echo $sfReport['uin']; ?>
                </span>
        <span class="w200 center">
                    <?php echo $sfReport['input_date']; ?>
                </span>
        <span class="w75 center">
                    <?php echo $sfReport['period_year'].'.'.$sfReport['period_month']; ?>
                </span>
        <span class="w200 center">
                    <?php echo $sfReport['status_curator']; ?>
                </span>
        <span class="w200 center">
                    <?php echo $sfReport['message_error']; ?>
                </span>
        <span class="w300">
                    <?php echo $sfReport['message_curator']; ?>
                </span>
        <span class="w-fit">
                     <form action="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="POST">
                         <input type="hidden" name="uinSfArch" value="<?php echo $sfReport['uin']?>" />
                         <input type="submit" class="button" name="uinSfArchDownload" value="просмотр" />
                     </form>
                </span>
    </div>
</div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-resultset">По данному инн записей в архиве не найдено</div>
<?php endif; ?>