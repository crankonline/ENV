<?php
/** @var $data array */
?>

<link rel="stylesheet" type="text/css" href="resources/css/ui-misc-form.css">
<link rel="stylesheet" type="text/css" href="resources/css/ui-misc-messages.css">
<style>
    .form .field > input[type="text"],
    .form .field > input[type="date"],
    .form .field > input[type="time"],
    .form .field > input[type="datetime-local"],
    .form .field > input[type="password"],
    .form .field > select,
    .form .field > textarea {
        min-width: 515px;
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

<form class="form" action="index.php?view=meta-gked&&action=add" method="POST">

    <div class="caption">Добавление ГКЭД</div>

    <div class="field">
        <label class="fixed">ГКЭД <span class="required">*</span>:</label>
        <input type="text" name="gked" value="<?php echo htmlspecialchars($_POST['gked'] ?? ''); ?>" placeholder="Укажите ГКЭД" required>
        <span class="hint">ГКЭД</span>
    </div>

    <div class="field">
        <label class="fixed">Наименование <span class="required">*</span>:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Укажите наименование" required>
        <span class="hint">Наименование вида деятельности</span>
    </div>

    <div class="field">
        <label class="fixed">ГКЭД родителя <span class="required">*</span>:</label>
        <input type="text" name="parent" value="<?php echo htmlspecialchars($_POST['parent'] ?? ''); ?>" placeholder="Укажите родительский ГКЭД" required>
        <span class="hint">ГКЭД родителя</span>
    </div>

    <div class="field buttons">
        <input type="submit" class="button" value="Добавить" />
        <input type="button" class="button" name="remove" value="Назад" onclick="window.location.href = '?view=meta-gked'" />
    </div>

</form>

