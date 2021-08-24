<script>hljs.initHighlightingOnLoad();</script>
<style>
    body {
        padding-bottom: 0;
    }
</style>
<form class="form" action="index.php" method="GET">

    <input type="hidden" name="view" value="<?php echo $this::AK_SOCHI_EDIT_STI_REPORT; ?>"/>
    <div class="caption">Поиск по UIN</div>

    <div class="field">
        <label for="uin" class="fixed">UIN:</label>
        <input type="text" name="uin" id="uin" maxlength="50" style="width: 550px" placeholder="введите UIN"
               value="<?php echo isset($_GET['uin']) ? htmlspecialchars($_GET['uin']) : '' ?>">
        <span class="hint">50 цифр</span>
    </div>

    <div class="field buttons">
        <input type="submit" id="search" class="button" value="Поиск"/>
    </div>

</form>

<?php if (isset($errors) && sizeof($errors)>0): ?>
    <?php foreach ($errors as $error): ?>
        <div class="failure"><?php echo $error; ?></div>
    <?php endforeach; ?>
    <?php return; ?>
<?php endif; ?>

<?php if (isset($success)): ?>
    <?php foreach ($success as $suc): ?>

        <div class="empty-resultset" id="success"><?= $suc ?></div>
    <?php endforeach; ?>
<?php endif; ?>


<pre style=" display:none;"><?php if (isset($length)): ?><?php print_r($length); ?><?php endif; ?></pre>

<?php if (isset($length['rep_xml_length'])): ?>
    <?php /** @var $available_size int|string */ ?>
    <?php if ($length['rep_xml_length'] > (int)$available_size) : ?>
        Превышен размер для отображения - <a
                href="index.php?view=<?php echo $this::AK_SOCHI_EDIT_STI_REPORT; ?>&type=<?php echo $_GET['type']; ?>&uin=<?php echo $_GET['uin']; ?>&sys-name=<?php echo $_GET['sys-name']; ?>&download=true">скачать</a>
    <?php else : ?>
        <pre style="text-align: left; display: none;">
            <code class="xml hljs">
                <?php /** @var $report string */ ?>
                <?php echo str_replace(">", "&gt;", str_replace("<", "&lt;", $report)); ?>
            </code>
        </pre>
        <form class="form" action="index.php?view=<?=$this::AK_SOCHI_EDIT_STI_REPORT?>&uin=<?=$_GET['uin']?>" method="POST">


            <div class="field buttons">
                <input type="submit" id="search" class="button" value="Сохранить"/>
                <input
                        type="button"
                        class="button"
                        value="Открыть данные об отчете"
                        onclick="window.open('index.php?view=curator-sti&uin=<?php echo $_GET['uin'] ?>')"
                />
            </div>

            <textarea name="xml" style="width: 100%; height: 800px;"><?php echo htmlspecialchars($report) ; ?></textarea>

            <div class="field buttons">
                <input type="submit" id="search" class="button" value="Сохранить"/>
                <input
                    type="button"
                    class="button"
                    value="Открыть данные об отчете"
                    onclick="window.open('index.php?view=curator-sti&uin=<?php echo $_GET['uin'] ?>')"
                />
            </div>

        </form>
    <?php endif; ?>
<?php endif; ?>
