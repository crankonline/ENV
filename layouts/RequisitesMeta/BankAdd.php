<?php
/** @var $ownershipForms array */
/** @var $data array */
/** @var $success bool */
?>
<style>
    .form .field > input[type="text"],
    .form .field > input[type="date"],
    .form .field > input[type="time"],
    .form .field > input[type="datetime-local"],
    .form .field > input[type="password"],
    .form .field > select,
    .form .field > textarea {
        min-width: 515px;
        max-width: 515px;
        display: inline-block;
    }

    .form .field > label {
        min-width: 300px;
    }
</style>

<?php if(!empty($success) || isset($_GET['success'])): ?>
    <div class="success">Сохранено успешно</div>
<?php endif; ?>

<?php if(!empty($errorMessage)): ?>
    <div class="failure"><?php echo $errorMessage; ?></div>
<?php endif; ?>

<form class="form" action="index.php?view=meta-bank&action=add" method="POST">

    <div class="caption">Добавление банка</div>

    <div class="field">
        <label class="fixed">БИК <span class="required">*</span>:</label>
        <input type="text" name="bik" pattern="^[0-9]{6}" maxlength="6" value="<?php echo htmlspecialchars($_POST['bik'] ?? ''); ?>" placeholder="Укажите БИК" required>
        <span class="hint">БИК (6 цифр)</span>
    </div>

    <div class="field">
        <label class="fixed">Наименование <span class="required">*</span>:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Укажите наименование" required>
        <span class="hint">Наименование ОПФ</span>
    </div>


    <div class="field">
        <label class="fixed">Адрес <span class="required">*</span>:</label>
        <textarea name="address" placeholder="Укажите адрес"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
        <span class="hint">Адрес</span>
    </div>

    <div class="field buttons">
        <input type="submit" class="button" value="Добавить" />
        <input type="button" class="button" name="remove" value="Назад" onclick="window.location.href = '?view=meta-bank'" />
    </div>

</form>
