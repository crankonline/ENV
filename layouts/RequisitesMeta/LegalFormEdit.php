<?php
    /** @var $ownershipForms array */
    /** @var $data array */
    /** @var $success bool */
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

<?php if(!empty($success)): ?>
    <div class="success">Сохранено успешно</div>
<?php endif; ?>

<?php if(!empty($errorMessage)): ?>
    <div class="failure"><?php echo $errorMessage; ?></div>
<?php endif; ?>

<form class="form" action="index.php?view=meta-legal-form&id=<?php echo $data['IDLegalForm']; ?>&action=edit" method="POST">

    <div class="caption">Редактирование организационно-правовой формы</div>

    <input type="hidden" name="id" value="<?php echo $data['IDLegalForm'] ?>" />

    <div class="field">
        <label for="eNumber" class="fixed">Форма собственности<span class="required">*</span>:</label>
        <select name = "ownershipform">
            <?php foreach ($ownershipForms as $i => $ownershipForm): ?>
                <option <?php if($i == ($_POST['ownershipform'] ?? $data['OwnershipFormID'])): ?>selected<?php endif; ?> value="<?php echo $i; ?>"><?php echo $ownershipForm; ?></option>
            <?php endforeach; ?>
        </select>
        <span class="hint">Форма собственности (выберите из списка)</span>
    </div>

    <div class="field">
        <label class="fixed">Наименование <span class="required">*</span>:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? $data['Name']); ?>" placeholder="Укажите наименование" required>
        <span class="hint">Наименование ОПФ</span>
    </div>

    <div class="field">
        <label class="fixed">Сокращенное наименование <span class="required">*</span>:</label>
        <input type="text" name="shortname" value="<?php echo htmlspecialchars($_POST['shortname'] ?? $data['ShortName']); ?>" placeholder="Укажите сокр. наименование" required>
        <span class="hint">Сокращенное наименование ОПФ</span>
    </div>

    <div class="field">
        <label class="fixed">Фасет <span class="required">*</span>:</label>
        <input type="text" name="facet" value="<?php echo htmlspecialchars($_POST['facet'] ?? $data['Facet']); ?>" placeholder="Укажите фасет" required>
        <span class="hint">Фасет (числово код)</span>
    </div>

    <div class="field buttons">
        <input type="submit" class="button" name="update" value="Изменить" />
        <input type="button" class="button" name="remove" value="Назад" onclick="window.location.href = '?view=meta-legal-form'" />
    </div>

</form>
