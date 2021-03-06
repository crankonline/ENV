<script>hljs.initHighlightingOnLoad();</script>
<?php if ($errors): ?>
    <?php foreach ($errors as $error): ?>
        <div class="failure"><?php echo $error; ?></div>
    <?php endforeach; ?>
    <?php return; ?>
<?php endif; ?>

<table style="display:none;">
    <tr>
        <td><b>OrgName</b></td>
        <td><b>Inn</b></td>
        <td><b>Owner</b></td>
        <td><b>PassportN</b></td>
        <td><b>Series</b></td>
        <td><b>Title</b></td>
        <td><b>DateStart</b></td>
        <td><b>DateFinish</b></td>
        <td><b>StatusMessage</b></td>
    </tr>
    <?php foreach ($certs as $cert) : ?>
        <tr>
            <td><?php echo $cert[0]->OrgName; ?></td>
            <td><?php echo $cert[0]->Inn; ?></td>
            <td><?php echo $cert[0]->Owner; ?></td>
            <td><?php echo $cert[0]->Passport->Number; ?></td>
            <td><?php echo $cert[0]->Passport->Series; ?></td>
            <td><?php echo $cert[0]->Title; ?></td>
            <td><?php echo $cert[0]->DateStart; ?></td>
            <td><?php echo $cert[0]->DateFinish; ?></td>
            <td><?php echo $cert[0]->StatusMessage; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<pre style='text-align:left; display: none;'>
<?php print_r($certs); ?>
</pre>
<?php
$innReq = '';
foreach ($certs as $certss) : ?>
    <?php
    if ($certss ?? null)
    foreach ($certss as $certificate) :
        $start = strtotime($certificate->DateStart);
        $finish = strtotime($certificate->DateFinish);

        $isInvalidSeries = !preg_match(
            '/^[A-Z0-9\-]+$/',
            $certificate->Passport->Series
        );

        $isInvalidNumber = !preg_match(
            '/^[A-Z0-9\-]+$/',
            $certificate->Passport->Number
        );

        $isActive = (($finish >= time()) && $certificate->SystemIsAccessible);

        if ($isInvalidSeries || $isInvalidNumber) {
            $temp = [];

            if ($isInvalidSeries) {
                $temp[] = 'серии';
            }

            if ($isInvalidNumber) {
                $temp[] = 'номере';
            }

            $invalidPassport = 'недопустимые символы в ' . implode(' и ', $temp);
        } else {
            $invalidPassport = null;
        }

        $class = $isActive ? 'active' : 'expired';

        $certificate->DateStart = date('d.m.Y H:i:s', $start);
        $certificate->DateFinish = date('d.m.Y H:i:s', $finish);

        $isTariff = (
            isset($certificate->TarifEDS)
            &&
            is_object($certificate->TarifEDS)
        );
        ?>

        <div class="pki-certificate <?php echo $class ?>">
            <?php echo "<pre style='text-align:left; display: none;'>";
            print_r($certificate);
            echo "</pre>"; ?>
            <div class="header">
                <span class="w150 center">
                    <b>ИНН:</b>
                    <br>
                    <?php echo htmlspecialchars($certificate->Inn); $innReq = htmlspecialchars($certificate->Inn); ?>
                </span>
                <span class="w300 center">
                    <b>Серийный номер:</b>
                    <br>
            <?php echo htmlspecialchars($certificate->CertNumber); ?>
                </span>
                <span class="w75 center">
                    <b>СОС:</b>
                    <br>
            <?php echo $certificate->SystemIsAccessible ? 'Да' : 'Нет'; ?>
                </span>
                <span class="w200 center">
                    <b>Роль:</b>
                    <br>
            <?php echo htmlspecialchars($certificate->Title); ?>
                </span>
                <span class="w200 center">
                    <b>Паспорт:</b>
                    <br>
            <?php
            echo
            htmlspecialchars($certificate->Passport->Series),
            ' ',
            htmlspecialchars($certificate->Passport->Number),
            $invalidPassport ? '<br><u>' . $invalidPassport . '</u>' : '';
            ?>
                </span>
                <span class="w-fit">
                    <b>ФИО владельца:</b>
                    <br>
            <?php echo htmlspecialchars($certificate->Owner); ?>
                </span>
            </div>
            <div class="details">
                <span>
                    <b>Организация:</b>
                    <?php echo htmlspecialchars($certificate->OrgName); ?>
                </span>
                <span>
                    <b>Дата выдачи:</b>
            <?php echo htmlspecialchars($certificate->DateStart); ?>
                </span>
                <span>
                    <b>Дата окончания:</b>
            <?php echo htmlspecialchars($certificate->DateFinish); ?>
                </span>

                <?php if ($isTariff): ?>
                    <span>
                        <b>Тип тарифа:</b>
            <?php echo htmlspecialchars($certificate->TarifEDS->TarifType); ?>
                    </span>
                    <span>
                        <b>Тариф:</b>
            <?php echo htmlspecialchars($certificate->TarifEDS->Tarif); ?>
                    </span>
                <?php endif; ?>

                <span>
                    <b>Сообщение о состоянии:</b>
            <?php echo htmlspecialchars($certificate->StatusMessage); ?>
                </span>

                <span>
                    <b>Тип сертификата:</b>
            <?php
            echo(
            isset ($certificate->Passport->EdsUsage)
                ? htmlspecialchars($certificate->Passport->EdsUsage)
                : (
            isset ($certificate->EdsUsage['Requisites-'][$certificate->Passport->Series . '|' . $certificate->Passport->Number]['EdsName'])
                ? htmlspecialchars($certificate->EdsUsage['Requisites-'][$certificate->Passport->Series . '|' . $certificate->Passport->Number]['EdsName'])
                : htmlspecialchars("не удалось сопоставить реквизиты")
            )
            );

            /*echo (
                isset ($certificate->EdsUsage['Requisites1'][$certificate->Passport->Series.'|'.$certificate->Passport->Number])
                ? htmlspecialchars($certificate->EdsUsage['Requisites1'][$certificate->Passport->Series.'|'.$certificate->Passport->Number]['EdsName'])
                : htmlspecialchars("не удалось сопоставить реквизиты")
            );*/
            ?>
                </span>
            </div>
        </div>
    <?php endforeach;
endforeach; ?>
<div class="field buttons">
    <?php if($this->isPermitted($this::AK_REQUISITES)): ?>
        <a href="index.php?view=<?php echo $this::AK_REQUISITES; ?>&inn=<?php echo $innReq; ?>" target="_blank" class="button">Текущие реквизиты</a>
        <a href="index.php?view=<?php echo $this::AK_REQUISITES; ?>&inn=<?php echo $innReq; ?>&uid=&date=<?php echo $nearestRequisites['DateTime']; ?>" target="_blank" class="button">Реквизиты во время сдачи</a>
    <?php endif; ?>
    <a href="index.php?view=<?php echo $this::AK_DIFF_REQUISITES; ?>&inn=<?php echo $innReq; ?>&date=<?php echo $nearestRequisites['DateTime']; ?>" target="_blank" class="button">Diff</a>

</div>
<pre style=" display:none;"><?php print_r($length);?></pre>

<?php if ($length['rep_xml_length'] > $available_size) : ?>
    Превышен размер для отображения - <a href="index.php?view=<?php echo $this::AK_REPORT_DECODE; ?>&type=<?php echo $_GET['type']; ?>&uin=<?php echo $_GET['uin']; ?>&sys-name=<?php echo $_GET['sys-name'];?>&download=true" >скачать</a>
<?php else : ?>
<pre style="text-align: left;">
    <code class="xml hljs">
<?php echo str_replace(">","&gt;",str_replace("<","&lt;",$report)); ?>
    </code>
</pre>
<textarea style="width: 100%; height: 800px; display:none;">
<?php echo htmlspecialchars($report)  ?>
</textarea>
<?php endif; ?>
