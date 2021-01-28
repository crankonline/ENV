<form class="form" action="index.php" method="GET">

    <input type="hidden" name="view" value="<?php echo $this::AK_REQUISITES; ?>" />
    <div class="caption">Просмотр профиля в службе реквизитов</div>

    <div class="field">
        <label for="inn" class="fixed">ИНН:</label>
        <input onkeyup="checkParams()" type="text" name="inn" id="inn" maxlength="14" placeholder="введите ИНН" value="<?php echo isset($_GET['inn']) ? htmlspecialchars($_GET['inn']) : '' ?>">
        <span class="hint">10 или 14 цифр</span>
    </div>

    <div class="field">
        <label for="uid" class="fixed">UID:</label>
        <input type="text" name="uid" id="uid" maxlength="23" style="width: 350px" placeholder="введите UID" value="<?php echo isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '' ?>">
        <span class="hint">23 цифры</span>
    </div>

    <div class="field buttons">
        <input type="submit" id="search" class="button" value="Поиск" />
    </div>

</form>

<?php if($this->isPermitted($this::AK_REQUISITES, $this::PMS_CAN_SEND_TUNDUK_REQUISITES)): ?>
<form class="form" id="ajax_form" method="POST" action="" style="display: none">
    <div class="field buttons">
        <input type="checkbox" id="rdioTunduk" name="rdio"/><label for="rdioTunduk">Tunduk</label>
        <input type="checkbox" id="rdioMF" name="rdio"/><label for="rdioMF">MF</label>
        <input type="submit" id="btn-tunduk" class="button disabled" value="Выгрузить" disabled/>
    </div>

</form>
<?php endif; ?>

<style>
    .button.disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

</style>

<div id="fountainG" style="display: none">
    <div id="fountainG_1" class="fountainG"></div>
    <div id="fountainG_2" class="fountainG"></div>
    <div id="fountainG_3" class="fountainG"></div>
    <div id="fountainG_4" class="fountainG"></div>
    <div id="fountainG_5" class="fountainG"></div>
    <div id="fountainG_6" class="fountainG"></div>
    <div id="fountainG_7" class="fountainG"></div>
    <div id="fountainG_8" class="fountainG"></div>
</div>
<div class="empty-resultset" id="tunduk-success"style="display: none"></div>
<div class="failure" id="tunduk-error"style="display: none"></div>

<script type="text/javascript" src="resources/js/utils.js"></script>
<script type="text/javascript" src="resources/js/jquery-2.1.4.min.js"></script>

<script type="text/javascript">

    function checkParams() {

        var inputInn = $('#inn').val();
        var button =  $("#btn-tunduk");

        if(inputInn.length >= 10) {
            button.css("opacity", "3");
            button.attr('disabled',false);
        } else {
            button.css("opacity", "0.65");
            button.attr('disabled',true);
        }
    }

    $( document ).ready(checkParams);


    $("#btn-tunduk").click(function() {

        if (!$("input[name='rdio']:checked").val()) {
            alert('Пожалуйста выберете метод отправки.');
            return false;
        } else {
            sendAjaxForm().then((data) => {
            }).catch((error) => {
            });
            return false;
        }

    });

    function sendAjaxForm() {

        return new Promise((resolve, reject) => {
            let tunWacc = $("#fountainG");
            let tunSuc = $("#tunduk-success");
            let tunErr = $("#tunduk-error");

            tunWacc.css('display', 'block');
            tunSuc.css('display', 'none');
            tunErr.css('display', 'none');

            function tuErr () {
                tunSuc.css('display', 'none');
                tunErr.css('display', 'block');
                tunWacc.css('display', 'none');

            };

            let tundukAct = $("input[id='rdioTunduk']:checked").val() ? "1" : "";
            let tundukMFAct = $("input[id='rdioMF']:checked").val() ? "1" : "";

            let postForm = {
                'inn-tunduk'     : $('#inn').val(),
                'tundukAct'      : tundukAct,
                'tundukMFAct'    : tundukMFAct
            };

            $.ajax({
                type:'POST',
                url:'index.php?view=requisites&action=tunduk',
                dataType:'json',
                data: postForm,
                success: function (data) {
                    let obj = data;

                    let textOutRes = '';
                    let res =  obj["result"];
                    if(typeof res !== 'undefined' && typeof res[0] !== 'undefined' && res[0].tundukAct == 'successTunduk') {
                        textOutRes = textOutRes + "Компания успешно отправлена в Тундук.";
                    }
                    if(typeof res !== 'undefined' && typeof res[1] !== 'undefined' && res[1].tundukMFAct == 'successTundukMF') {
                        textOutRes = textOutRes + "\n\rКомпания успешно отправлена в ТундукMF.";
                    }
                    tunSuc.text(textOutRes);
                    tunSuc.css('display', 'block');
                    tunErr.css('display', 'none');
                    tunWacc.css('display', 'none');

                    let err = obj["error"];
                    let textOutErr = '';
                    if(typeof err !== 'undefined' && typeof err[0] !== 'undefined' && err[0].tundukAct == "noINNTunduk") {
                        // textOutErr = textOutErr + "Сертификатов для выгрузки не найдено. (Tunduk)";
                    }
                    if(typeof err !== 'undefined' && typeof err[1] !== 'undefined' && err[1].tundukMFAct == "noINNTunduk") {
                        // textOutErr = textOutErr + "\n\rСертификатов для выгрузки не найдено. (TundukMf)";
                    }
                    if(textOutErr.length>0) {
                        tunErr.text(textOutErr);
                        tuErr();

                    }
                    resolve(data);
                },
                error: function (error) {
                    tunErr.text("Неизвестная ошибка. Пожалуйста повторите позже " + error);
                    tuErr();
                    reject(error);
                },
                fail: function (fail) {
                }
            })
        });


    };
</script>

<?php if($errors): ?>
    <?php foreach($errors as $error): ?>
    <div class="failure"><?php echo $error; ?></div>
    <?php endforeach; ?>
    <?php return; ?>
<?php endif; ?>

<?php if(!isset($requisites)): ?>
    <div class="empty-resultset">Введите данные для поиска.</div>
    <?php return; ?>
<?php endif; ?>

<?php
if($balance):
    $class = 'successful';
else:
    $class = 'failed';
endif;
?>
<div class="form <?php echo $class; ?>">
    <div class="caption">
        Cумма на счете: <b><?php echo number_format((float)$balance, 2, '.', ' '); ?></b> сом
    </div>
</div>

<?php $usageStatus = empty($requisites->usageStatus) ? null : $requisites->usageStatus; ?>

<?php
if($usageStatus):?>


<?php   if($usageStatus->isActive):
        $class = 'successful';
        $name  = 'ведется';?>
<script>
$("#ajax_form").css('display', 'block');
</script>
<?php       else:
      $class = 'failed';
       $name  = 'не ведется';  ?>
<script>
    $("#ajax_form").css('display', 'block');
</script>
<?php   endif;

else:
    $class = 'default';
    $name  = 'нет данных';
endif;
?>
<div class="form <?php echo $class; ?>">
    <div class="caption">
        Обслуживание: <b><?php echo $name; ?></b>
    </div>

    <div class="view-section">
        <?php if($usageStatus): ?>
            <div class="field">
                <span class="text fixed">Дата и время:</span>
                <span class="text"><?php
                    $dateTime = \DateTime::createFromFormat(
                        \DateTime::ISO8601,
                        $usageStatus->dateTime
                    );

                    echo $dateTime->format('d.m.Y H:i:s');
                ?></span>
            </div>

            <div class="field">
                <span class="text fixed">Описание:</span>
                <pre><?php
                    echo htmlspecialchars($usageStatus->description);
                ?></pre>
            </div>
        <?php else: ?>
            <div class="field">
                <span class="text">Данные отсутствуют.</span>
            </div>
        <?php endif; ?>

        <?php if($this->isPermitted($this::AK_REQUISITES, $this::PMS_CAN_CHANGE_USAGE_STATUS)): ?>
            <div class="caption">Изменение состояния</div>
            <form action="index.php?<?php echo $_SERVER['QUERY_STRING']; ?>" method="POST">
                <div class="field">
                    <span class="text fixed">Обслуживание:</span>
                    <label>
                        <input type="radio" name="status" value="0" checked> Не ведется
                    </label>

                    <label>
                        <input type="radio" name="status" value="1"> Ведется
                    </label>
                </div>

                <div class="field">
                    <span class="text fixed">Комментарий:</span>
                    <textarea name="description"></textarea>
                </div>

                <div class="field buttons">
                    <input type="submit" class="button" name="setUsageStatus" value="Изменить" />
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php $common = $requisites->common; ?>
<table class="data requisites">
    <caption>Реквизиты</caption>
    <tr>
        <th colspan="2">UID:</th>
        <td>
            <?php echo $requisites->uid; ?>
            <?php if($this->isPermitted($this::AK_UID_PARSER)): ?>
                <a href="index.php?view=<?php echo $this::AK_UID_PARSER; ?>&uid=<?php echo $requisites->uid ?>" target="_blank">Информация по UID</a>
            <?php endif; ?>
        </td>
    </tr>

<?php
if($common):
    $mainActivity     = $common->mainActivity;
    $capitalForm      = $common->capitalForm;
    $legalForm        = $common->legalForm;
    $managementForm   = $common->managementForm;
    $civilLegalStatus = $common->civilLegalStatus;
    $chiefBasis       = $common->chiefBasis;
    $bank             = $common->bank;
    $juristicAddress  = $common->juristicAddress;
    $physicalAddress  = $common->physicalAddress;
    $representatives  = $common->representatives;
?>
    <tr>
        <th colspan="2">ИНН:</th>
        <td>
            <?php echo $common->inn; ?>
            <?php if($this->isPermitted($this::AK_SOCHI)): ?>
                <a href="index.php?view=<?php echo $this::AK_SOCHI; ?>&inn=<?php echo $common->inn; ?>" target="_blank">СОчИ</a>
            <?php endif; ?>
            <?php if($this->isPermitted($this::AK_SEO)): ?>
                <a href="index.php?view=<?php echo $this::AK_SEO; ?>&inn=<?php echo $common->inn; ?>" target="_blank">SEO</a>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th colspan="2">Наименование:</th>
        <td><?php
            echo htmlspecialchars($common->name);
        ?></td>
    </tr>
    <tr>
        <th colspan="2">Полное наименование:</th>
        <td><?php
            echo htmlspecialchars($common->fullName);
        ?></td>
    </tr>
    <tr>
        <th colspan="2">ОКПО:</th>
        <td><?php
            echo htmlspecialchars($common->okpo);
        ?></td>
    </tr>
    <tr>
        <th colspan="2">Регистрационный номер Социального Фонда:</th>
        <td><?php
            echo htmlspecialchars($common->rnsf);
        ?></td>
    </tr>
    <tr>
        <th colspan="2">Регистрационный номер Министерства Юстиции:</th>
        <td><?php
            echo htmlspecialchars($common->rnmj);
        ?></td>
    </tr>
    <tr>
        <th colspan="2">Электронная почта:</th>
        <td><?php
            echo htmlspecialchars($common->eMail);
        ?></td>
    </tr>
    <?php if($mainActivity): ?>
        <tr>
            <th colspan="2">Оcновной вид деятельности:</th>
            <td><?php
                echo htmlspecialchars($mainActivity->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($capitalForm): ?>
        <tr>
            <th colspan="2">Форма участия в капитале:</th>
            <td><?php
                echo htmlspecialchars($capitalForm->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($legalForm): ?>
        <tr>
            <th colspan="2">Форма собственности:</th>
            <td><?php
                echo htmlspecialchars($legalForm->ownershipForm->name);
            ?></td>
        </tr>
        <tr>
            <th colspan="2">Организационно-правовая форма:</th>
            <td><?php
                echo htmlspecialchars($legalForm->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($managementForm): ?>
        <tr>
            <th colspan="2">Форма правления:</th>
            <td><?php
                echo htmlspecialchars($managementForm->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($civilLegalStatus): ?>
        <tr>
            <th colspan="2">Гражданско-правовой статус:</th>
            <td><?php
                echo htmlspecialchars($civilLegalStatus->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($bank): ?>
        <tr>
            <th rowspan="4" class="center">Банковские данные</th>
            <th>БИК:</th>
            <td><?php
                echo htmlspecialchars($bank->id);
            ?></td>
        </tr>
        <tr>
            <th>Наименование:</th>
            <td><?php
                echo htmlspecialchars($bank->name);
            ?></td>
        </tr>
        <tr>
            <th>Адрес:</th>
            <td><?php
                echo htmlspecialchars($bank->address);
            ?></td>
        </tr>
        <tr>
            <th>Расчетный счет:</th>
            <td><?php
                echo htmlspecialchars($common->bankAccount);
            ?></td>
        </tr>
    <?php endif; ?>

<?php
    $addresses = [
        'Юридический' => $juristicAddress,
        'Физический'  => $physicalAddress
    ];

    foreach($addresses as $type => $address):
        if(!$address):
            continue;
        endif;

        $settlement = $address->settlement;
        $district   = $settlement->district;
        $region     = $district ? $district->region : $settlement->region;
?>
        <tr>
            <th colspan="3" class="center"><?php echo $type; ?> адрес:</th>
        </tr>
        <tr>
            <th colspan="2">Индекс:</th>
            <td><?php
                echo htmlspecialchars($address->postCode);
            ?></td>
        </tr>

        <?php if($region): ?>
            <tr>
                <th colspan="2">Область:</th>
                <td><?php
                    echo htmlspecialchars($region->name);
                ?></td>
            </tr>
        <?php endif; ?>

        <?php if($district): ?>
            <tr>
                <th colspan="2">Район:</th>
                <td><?php
                    echo htmlspecialchars($district->name);
                ?></td>
            </tr>
        <?php endif; ?>

        <tr>
            <th colspan="2">Населенный пункт:</th>
            <td><?php
                echo htmlspecialchars($settlement->name);
            ?></td>
        </tr>
        <tr>
            <th colspan="2">Улица / Микрорайон:</th>
            <td><?php
                echo htmlspecialchars($address->street);
            ?></td>
        </tr>
        <tr>
            <th colspan="2">Дом / Строение:</th>
            <td><?php
                echo htmlspecialchars($address->building);
            ?></td>
        </tr>

        <?php if($address->apartment): ?>
            <tr>
                <th colspan="2">Квартира / Офис:</th>
                <td><?php
                    echo htmlspecialchars($address->apartment);
                ?></td>
            </tr>
        <?php endif; ?>
    <?php
    endforeach;

    $isRepresentativeEditor = $this->isPermitted($this::AK_REPRESENTATIVES_EDITOR);

    foreach($representatives as $rep):
        $person        = $rep->person;
        $passport      = $person ? $person->passport : null;
        $roles         = $rep->roles;
        $position      = $rep->position;
        $edsUsageModel = $rep->edsUsageModel;
        $phone         = $rep->phone;
        $isValidSeries = preg_match('/^[A-Z0-9\-]+$/', $passport->series);
        $isValidNumber = preg_match('/^[A-Z0-9\-]+$/', $passport->number);
?>
        <tr>
            <th colspan="3" class="center"><?php
                $isChief = false;

                $tmp = '';

                $tmpArr = [];

                foreach($roles as $role):

                    if(!in_array($role->id, $tmpArr)):
                        $tmp .= htmlspecialchars($role->name) . ',';
                    endif;

                    if($role->id == $this::ROLES_CHIEF):
                        $isChief = true;
                    endif;

                    $tmpArr[] = $role->id;

                endforeach;

                $tmp = trim($tmp, ',');

                echo str_replace(',', '<br>+<br>', $tmp);
            ?></th>
        </tr>

        <?php if($passport): ?>
            <tr>
                <th rowspan="4" class="center">
                    Паспортные данные

                    <?php if($isRepresentativeEditor): ?>
                        <br>
                        <a href="index.php?view=<?php echo $this::AK_REPRESENTATIVES_EDITOR; ?>&series=<?php echo $passport->series; ?>&number=<?php echo $passport->number; ?>" target="_blank">Правка</a>
                    <?php endif; ?>
                </th>
                <th>Серия:</th>
                <td>
                    <?php echo htmlspecialchars($passport->series); ?>
                    <?php if(!$isValidSeries): ?>
                        (недопустимые символы)
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Номер:</th>
                <td>
                    <?php echo htmlspecialchars($passport->number); ?>
                    <?php if(!$isValidNumber): ?>
                        (недопустимые символы)
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Орган выдачи:</th>
                <td><?php
                    echo htmlspecialchars($passport->issuingAuthority);
                ?></td>
            </tr>
            <tr>
                <th>Дата выдачи:</th>
                <td><?php
                    echo date('d.m.Y', strtotime($passport->issuingDate));
                ?></td>
            </tr>
        <?php endif; ?>

        <?php
            if($person):
                $tmp = array_filter([
                    $person->surname,
                    $person->name,
                    $person->middleName
                ]);
        ?>
            <tr>
                <th colspan="2">Фамилия, Имя и Отчество:</th>
                <td><?php
                    echo htmlspecialchars(implode(' ', $tmp));
                ?></td>
            </tr>
            <tr>
                <th colspan="2">Пин:</th>
                <td><?php
                            echo htmlspecialchars($person->pin);
                        ?></td>
            </tr>
        <?php endif; ?>

        <tr>
            <th colspan="2">Рабочий телефон:</th>
            <td><?php
                echo htmlspecialchars($rep->phone);
            ?></td>
        </tr>

        <?php if($position): ?>
            <tr>
                <th colspan="2">Должность:</th>
                <td><?php
                    echo htmlspecialchars($position->name);
                ?></td>
            </tr>
        <?php endif; ?>

        <?php if($isChief): ?>
            <tr>
                <th colspan="2">Основание для занятия должности:</th>
                <td><?php
                    echo htmlspecialchars($chiefBasis->name);
                ?></td>
            </tr>
        <?php endif; ?>

        <?php if($edsUsageModel): ?>
            <tr>
                <th colspan="2">Модель использования ЭЦП:</th>
                <td><?php
                    echo htmlspecialchars($edsUsageModel->name);
                ?></td>
            </tr>

            <?php if($rep->deviceSerial): ?>
                <tr>
                    <th colspan="2">Серийный номер устройства:</th>
                    <td><?php
                        echo htmlspecialchars($rep->deviceSerial);
                    ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>
<?php
    endforeach;
endif;

$sf = $requisites->sf;

if($sf):
    $tariff = $sf->tariff;
    $region = $sf->region;
?>
    <?php if($tariff): ?>
        <tr>
            <th rowspan="2" class="center">СФ</th>
            <th>Тариф:</th>
            <td><?php
                echo htmlspecialchars($tariff->id . ' - ' . $tariff->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($region): ?>
        <tr>
            <th>Район:</th>
            <td><?php
                echo htmlspecialchars($region->id . ' - ' . $region->name);
            ?></td>
        </tr>
    <?php endif; ?>
<?php
endif;

$sti = $requisites->sti;

if($sti):
    $regionDefault = $sti->regionDefault;
    $regionReceive = $sti->regionReceive;
?>
    <?php if($regionDefault): ?>
        <tr>
            <th rowspan="2" class="center">ГНС</th>
            <th>Основной район:</th>
            <td><?php
                echo htmlspecialchars($regionDefault->id . ' - ' . $regionDefault->name);
            ?></td>
        </tr>
    <?php endif; ?>

    <?php if($regionReceive): ?>
        <tr>
            <th>Район предоставления:</th>
            <td><?php
                echo htmlspecialchars($regionReceive->id . ' - ' . $regionReceive->name);
            ?></td>
        </tr>
    <?php endif; ?>
<?php endif; ?>
</table>
<?php
if($bindings !== null):
    $person   = $bindings->person;
    $root     = $bindings->root;
    $bindings = $bindings->bindings;
?>
    <br>
    <table class="data consulting">
        <caption>Комплексное ЭЦП / Консалтинг</caption>
        <tr>
            <th>Пользователь:</th>
            <td><?php
                $tmp = array_filter([
                    $person->surname,
                    $person->name,
                    $person->middleName
                ]);

                echo htmlspecialchars(implode(' ', $tmp));
            ?>
            </td>
        </tr>
        <tr>
            <th colspan="2" class="center">Корневая компания:</th>
        </tr>

        <?php if($root): ?>
            <tr>
                <th>ИНН:</th>
                <td>
                    <a href="index.php?view=requisites&inn=<?php echo $root->inn; ?>" target="_blank"><?php
                        echo $root->inn;
                    ?></a>
                </td>
            </tr>
            <tr>
                <th>Наименование:</th>
                <td><?php
                    echo htmlspecialchars($root->name);
                ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="2">Не задана</td>
            </tr>
        <?php endif; ?>

        <tr>
            <th colspan="2" class="center">Обслуживаемые компании:</th>
        </tr>

        <?php foreach($bindings as $index => $binding): ?>
            <tr>
                <td class="center">
                    <a href="index.php?view=requisites&inn=<?php echo $binding->inn; ?>" target="_blank"><?php
                        echo htmlspecialchars($binding->inn);
                    ?></a>
                </td>
                <td><?php
                    echo htmlspecialchars($binding->name);
                ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if($certificates): ?>
<br>
<?php
    foreach($certificates as $index => $certificate):
        $start  = strtotime($certificate->DateStart);
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

        if($isInvalidSeries || $isInvalidNumber){
            $temp = [];

            if($isInvalidSeries){
                $temp[] = 'серии';
            }

            if($isInvalidNumber){
                $temp[] = 'номере';
            }

            $invalidPassport = 'недопустимые символы в ' . implode(' и ', $temp);
        } else {
            $invalidPassport = null;
        }

        $class = $isActive ? 'active' : 'expired';

        $certificate->DateStart  = date('d.m.Y H:i:s', $start);
        $certificate->DateFinish = date('d.m.Y H:i:s', $finish);

        $isTariff = (
            isset($certificate->TarifEDS)
            &&
            is_object($certificate->TarifEDS)
        );
?>
        <div class="pki-certificate <?php echo $class ?>">
            <div class="header">
                <span class="w150 center">
                    <b>ИНН:</b>
                    <br>
                    <?php echo htmlspecialchars($certificate->Inn); ?>
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

                <?php if($isTariff): ?>
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
                        echo (
                            isset ($certificate->Passport->EdsUsage)
                            ? htmlspecialchars($certificate->Passport->EdsUsage)
                            : (
                                isset ($certificate->EdsUsage['Requisites-'][$certificate->Passport->Series.'|'.$certificate->Passport->Number]['EdsName'])
                                ? htmlspecialchars($certificate->EdsUsage['Requisites-'][$certificate->Passport->Series.'|'.$certificate->Passport->Number]['EdsName'])
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
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-resultset">В PKI сведений не найдено</div>
<?php endif; ?>
