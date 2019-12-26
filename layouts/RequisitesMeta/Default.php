<!--accordeon-->
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<!--datatable-->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.jqueryui.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.0.0/css/scroller.jqueryui.min.css">


<!--accordeon-->
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<!--<script src="https://code.jquery.com/jquery-1.12.4.js"></script>-->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!--datatable-->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.jqueryui.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.0.0/js/dataTables.scroller.min.js"></script>



<script>
    $(function () {
        $("#accordion").accordion({ clearStyle: true, autoHeight: false, heightStyle: "content"  });
        $( ".resizable" ).resizable();
        $( "#legalForm").resizable();
        $( "#bank").resizable();








    });

    // var tips = $( ".validateTips" );
    $.fn.updateTips = function ( t ) {
        $( ".validateTips" )
            .text( t )
            .addClass( "ui-state-highlight" );
        setTimeout(function() {
            $( ".validateTips" ).removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    $.fn.checkLength = function ( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            $.fn.updateTips( "Длина " + n + " должен быть между " +
                min + " и " + max + "." );
            return false;
        } else {
            return true;
        }
    }


    /**
     * Обявление таблиц
     **/
    $(document).ready(function() {
        $('#legalForm').DataTable( {
            ajax: {
                "dataType": 'json',
                "contentType": "application/json; charset=utf-8",
                "type": "POST",
                "url":"index.php?view=requisitesMeta&action=getLegalFormJson",
                "dataSrc": function (json) {
                    return json;
                }
            },
            columns: [
                {data: 'IDLegalForm'},
                {data: 'OwnershipFormID'},
                {data: 'Name'},
                {data: 'ShortName'},
                {data: 'Facet'}
            ],
            deferRender:    true,
            scrollY:        '50vh',
            scrollCollapse: true,
            scroller:       true
        } );

        $('#bank').DataTable( {
            ajax: {
                "dataType": 'json',
                "contentType": "application/json; charset=utf-8",
                "type": "POST",
                "url":"index.php?view=requisitesMeta&action=getBankJson",
                "dataSrc": function (json) {
                    return json;
                }
            },
            columns: [
                {data: 'IDBank'},
                {data: 'Name'},
                {data: 'Address'}
            ],
            deferRender:    true,
            scrollY:        '50vh',
            scrollCollapse: true,
            scroller:       true
        } );

        $('#activity').DataTable( {
            ajax: {
                "dataType": 'json',
                "contentType": "application/json; charset=utf-8",
                "type": "POST",
                "url":"index.php?view=requisitesMeta&action=getActivityJson",
                "dataSrc": function (json) {
                    return json;
                }
            },
            columns: [
                {data: 'IDActivity'},
                {data: 'ActivityID'},
                {data: 'Name'},
                {data: 'Gked'}
            ],
            deferRender:    true,
            scrollY:        '50vh',
            scrollCollapse: true,
            scroller:       true
        } );

        $('#chiefBasis').DataTable( {
            ajax: {
                "dataType": 'json',
                "contentType": "application/json; charset=utf-8",
                "type": "POST",
                "url":"index.php?view=requisitesMeta&action=getChiefBasisJson",
                "dataSrc": function (json) {
                    return json;
                }
            },
            columns: [
                {data: 'IDChiefBasis'},
                {data: 'Name'}
            ],
            deferRender:    true,
            scrollY:        '50vh',
            scrollCollapse: true,
            scroller:       true
        } );



        $("#accordion").click(function(){

            $(this).next("va-content").slideToggle("slow")
                .siblings("va-content:visible").slideUp("slow");
            $(this).toggleClass(".va-content");
            $(this).siblings("va-heading");

        });

        /**
         * Обработка диалоговых окон
         **/

        /** правовая собственность **/
        $('#legalForm').on('click', 'tbody td', function() {
            $("#dialog-form-legalForm-save-message").text("");
            $(".validateTips").text("Все поля должны быть заполнены.");

            /** dialog */
            var dialog, form,

                name = $( "#name" ),
                short = $( "#short" ),
                id = $( "#id" ),
                allFields = $( [] ).add( name ).add( short ).add( id ),
                tips = $( ".validateTips" );

            function editLaw() {
                var valid = true;
                allFields.removeClass( "ui-state-error" );

                valid = valid && $.fn.checkLength( name, "Name", 3, 100 );
                valid = valid && $.fn.checkLength( short, "ShortName", 1, 5 );
                valid = valid && $.fn.checkLength( id, "IdLegalForm", 1, 8 );

                var jqxhr = $.ajax( {
                    method: "POST",
                    url: "index.php?view=requisitesMeta&action=editLegalForm",
                    data: { id: id.val(), name: name.val(), short: short.val() }
                } )
                    .done(function(ret) {
                        //location.reload();

                        if($.parseJSON(ret)['return']) {
                            var table = $('#legalForm').DataTable();
                            table.ajax.reload();

                            $("#dialog-form-legalForm-save-message").text("Сохранено - " + $.parseJSON(ret)['return']);
                        } else {
                            $("#dialog-form-legalForm-save-message").text("Ошибка - " + $.parseJSON(ret)['return']);
                        }
                    })
                    .fail(function() {
//                        alert( "error" );
                    });

                return valid;
            }

            dialog = $( "#dialog-form" ).dialog({
                autoOpen: false,
                height: 400,
                width: 650,
                modal: true,
                buttons: {
                    "Сохранить": function() {console.log('dialog save'); editLaw(); },
                    "Cancel": function() {
                        dialog.dialog( "close" );
                    }
                },
                close: function() {
                    form[ 0 ].reset();
                    allFields.removeClass( "ui-state-error" );
                }
            });

            form = dialog.find( "form" ).on( "submit", function( event ) {
                event.preventDefault();
                console.log('form submit');
                editLaw();
            });

            /** dialog end */

            $("#id").val(this.parentNode.childNodes.item(0).textContent);
            $("#name").val(this.parentNode.childNodes.item(2).textContent);
            $("#short").val(this.parentNode.childNodes.item(3).textContent);


            dialog.dialog( "open" );
        });

        /** банк **/
        $('#bank').on('click', 'tbody td', function() {
            $("#dialog-form-bank-save-message").text("");
            $(".validateTips").text("Все поля должны быть заполнены.");

            /** dialog */
            var dialog, form,

                name = $( "#bank-name" ),
                address = $( "#bank-address" ),
                id = $( "#bank-id" ),
                allFields = $( [] ).add( name ).add( address ).add( id ),
                tips = $( ".validateTips" );

            function edit() {
                var valid = true;
                allFields.removeClass( "ui-state-error" );

                valid = valid && $.fn.checkLength( name, "Name", 3, 100 );
                valid = valid && $.fn.checkLength( address, "ShortName", 1, 100 );
                valid = valid && $.fn.checkLength( id, "IdLegalForm", 1, 6 );

                var jqxhr = $.ajax( {
                    method: "POST",
                    url: "index.php?view=requisitesMeta&action=editBank",
                    data: { bankId: id.val(), bankName: name.val(), bankAddress: address.val() }
                } )
                    .done(function(ret) {
                        //location.reload();
                        if($.parseJSON(ret)['return']) {
                            var table = $('#bank').DataTable();
                            table.ajax.reload();

                            $("#dialog-form-bank-save-message").text("Сохранено - " + $.parseJSON(ret)['return']);
                        } else {
                            $("#dialog-form-bank-save-message").text("Ошибка - " + $.parseJSON(ret)['return']);
                        }

                    })
                    .fail(function() {
//                        alert( "error" );
                    });

                return valid;
            }

            dialog = $( "#dialog-form-bank" ).dialog({
                autoOpen: false,
                height: 400,
                width: 650,
                modal: true,
                buttons: {
                    "Сохранить": function() {console.log('dialog save'); edit(); },
                    "Cancel": function() {
                        dialog.dialog( "close" );
                    }
                },
                close: function() {
                    form[ 0 ].reset();
                    allFields.removeClass( "ui-state-error" );
                }
            });

            form = dialog.find( "form" ).on( "submit", function( event ) {
                event.preventDefault();
                console.log('form submit');
                edit();
            });

            /** dialog end */

//            console.log(this.parentNode.childNodes);
            $("#bank-id").val(this.parentNode.childNodes.item(0).textContent);
            $("#bank-name").val(this.parentNode.childNodes.item(1).textContent);
            $("#bank-address").val(this.parentNode.childNodes.item(2).textContent);


            dialog.dialog( "open" );
        });

        /** гкед  activity **/
        $('#activity').on('click', 'tbody td', function() {
            $("#dialog-form-activity-save-message").text("");
            $(".validateTips").text("Все поля должны быть заполнены.");

            /** dialog */
            var dialog, form,

                name = $( "#activity-name" ),
                gked = $( "#activity-gked" ),
                id = $( "#activity-id-activity" ),
                activity = $( "#activity-activity-id" ),
                allFields = $( [] ).add( name ).add( gked ).add( id ).add( activity ),
                tips = $( ".validateTips" );

            function edit() {
                var valid = true;
                allFields.removeClass( "ui-state-error" );

                valid = valid && $.fn.checkLength( name, "Name", 3, 100 );
                valid = valid && $.fn.checkLength( gked, "GKED", 1, 100 );
                valid = valid && $.fn.checkLength( id, "ID", 1, 8 );
                valid = valid && $.fn.checkLength( activity, "ActivityID", 1, 8 );

                var jqxhr = $.ajax( {
                    method: "POST",
                    url: "index.php?view=requisitesMeta&action=editActivity",
                    data: { id: id.val(), activityId: activity.val(), activityName: name.val(), activityGked: gked.val() }
                } )
                    .done(function(ret) {
                        //location.reload();
                        if($.parseJSON(ret)['return']) {
                            var table = $('#activity').DataTable();
                            table.ajax.reload();

                            $("#dialog-form-activity-save-message").text("Сохранено - " + $.parseJSON(ret)['return']);
                        } else {
                            $("#dialog-form-activity-save-message").text("Ошибка - " + $.parseJSON(ret)['return']);
                        }

                    })
                    .fail(function() {
//                        alert( "error" );
                    });

                return valid;
            }

            dialog = $( "#dialog-form-activity" ).dialog({
                autoOpen: false,
                height: 400,
                width: 650,
                modal: true,
                buttons: {
                    "Сохранить": function() {console.log('dialog save'); edit(); },
                    "Cancel": function() {
                        dialog.dialog( "close" );
                    }
                },
                close: function() {
                    form[ 0 ].reset();
                    allFields.removeClass( "ui-state-error" );
                }
            });

            form = dialog.find( "form" ).on( "submit", function( event ) {
                event.preventDefault();
                console.log('form submit');
                edit();
            });

            /** dialog end */

//            console.log(this.parentNode.childNodes);
            $("#activity-id-activity").val(this.parentNode.childNodes.item(0).textContent);
            $("#activity-activity-id").val(this.parentNode.childNodes.item(1).textContent);
            $("#activity-name").val(this.parentNode.childNodes.item(2).textContent);
            $("#activity-gked").val(this.parentNode.childNodes.item(3).textContent);


            dialog.dialog( "open" );
        });

        /** основание для занимаемой должности ChiefBasis **/
        $('#chiefBasis').on('click', 'tbody td', function() {
            $("#dialog-form-chiefBasis-save-message").text("");
            $(".validateTips").text("Все поля должны быть заполнены.");

            /** dialog */
            var dialog, form,

                name = $( "#chiefBasis-name" ),
                id = $( "#chiefBasis-id" ),
                allFields = $( [] ).add( name ).add( id ),
                tips = $( ".validateTips" );

            function edit() {
                var valid = true;
                allFields.removeClass( "ui-state-error" );

                valid = valid && $.fn.checkLength( name, "Name", 3, 100 );
                valid = valid && $.fn.checkLength( id, "ID", 1, 8 );

                var jqxhr = $.ajax( {
                    method: "POST",
                    url: "index.php?view=requisitesMeta&action=editChiefBasis",
                    data: { id: id.val(), name: name.val() }
                } )
                    .done(function(ret) {
                        //location.reload();
                        if($.parseJSON(ret)['return']) {
                            var table = $('#chiefBasis').DataTable();
                            table.ajax.reload();

                            $("#dialog-form-chiefBasis-save-message").text("Сохранено - " + $.parseJSON(ret)['return']);
                        } else {
                            $("#dialog-form-chiefBasis-save-message").text("Ошибка - " + $.parseJSON(ret)['return']);
                        }

                    })
                    .fail(function() {
//                        alert( "error" );
                    });

                return valid;
            }

            dialog = $( "#dialog-form-ChiefBasis" ).dialog({
                autoOpen: false,
                height: 400,
                width: 650,
                modal: true,
                buttons: {
                    "Сохранить": function() {console.log('dialog save'); edit(); },
                    "Cancel": function() {
                        dialog.dialog( "close" );
                    }
                },
                close: function() {
                    form[ 0 ].reset();
                    allFields.removeClass( "ui-state-error" );
                }
            });

            form = dialog.find( "form" ).on( "submit", function( event ) {
                event.preventDefault();
                console.log('form submit');
                edit();
            });

            /** dialog end */

//            console.log(this.parentNode.childNodes);
            $("#chiefBasis-id").val(this.parentNode.childNodes.item(0).textContent);
            $("#chiefBasis-name").val(this.parentNode.childNodes.item(1).textContent);


            dialog.dialog( "open" );
        });

        /** добавление банка **/
        $('#add-bank-button').on('click', function(){
            /** dialog */
            var dialog, form,

                name = $( "#bank-name" ),
                address = $( "#bank-address" ),
                id = $( "#bank-id" ),
                allFields = $( [] ).add( name ).add( address ).add( id );


            function edit() {
                var valid = true;
                allFields.removeClass( "ui-state-error" );

                valid = valid && $.fn.checkLength( name, "Name", 3, 100 );
                valid = valid && $.fn.checkLength( address, "ShortName", 1, 100 );
                valid = valid && $.fn.checkLength( id, "IdLegalForm", 1, 6 );

                var jqxhr = $.ajax( {
                    method: "POST",
                    url: "index.php?view=requisitesMeta&action=addBank",
                    data: { bankId: id.val(), bankName: name.val(), bankAddress: address.val() }
                } )
                    .done(function(ret) {
                        //location.reload();
                        if($.parseJSON(ret)['return']) {
                            var table = $('#bank').DataTable();
                            table.ajax.reload();

                            $("#dialog-form-bank-save-message").text("Сохранено - " + $.parseJSON(ret)['return']);
                        } else {
                            $("#dialog-form-bank-save-message").text("Ошибка - " + $.parseJSON(ret)['return']);
                        }

                    })
                    .fail(function() {
//                        alert( "error" );
                    });

                return valid;
            }

            dialog = $( "#dialog-form-bank" ).dialog({
                autoOpen: false,
                height: 400,
                width: 650,
                modal: true,
                buttons: {
                    "Добавить": function() {console.log('dialog save'); edit(); },
                    "Cancel": function() {
                        dialog.dialog( "close" );
                    }
                },
                close: function() {
                    form[ 0 ].reset();
                    allFields.removeClass( "ui-state-error" );
                }
            });

            form = dialog.find( "form" ).on( "submit", function( event ) {
                event.preventDefault();
                console.log('form submit');
                edit();
            });

            /** dialog end */

//            console.log(this.parentNode.childNodes);
//            $("#bank-id").val(this.parentNode.childNodes.item(0).textContent);
//            $("#bank-name").val(this.parentNode.childNodes.item(1).textContent);
//            $("#bank-address").val(this.parentNode.childNodes.item(2).textContent);


            dialog.dialog( "open" );
        });

    } );

</script>

<div id="dialog-form" title="Редактирование (LegalForm) Форма собственности" style="display: none">
    <p class="validateTips">Все поля должны быть заполнены.</p>

    <form>
        <fieldset>
            <table width="100%">
                <tr>
                    <td><label for="id">IDLegalForm</label></td>
                    <td><input type="text" name="id" id="id" value="" class="text ui-widget-content ui-corner-all" style="width: 100% " readonly></td>
                </tr>
                <tr>
                    <td><label for="name">Name</label></td>
                    <td><input type="text" name="name" id="name" value="" class="text ui-widget-content ui-corner-all" style="width: 100%"></td>
                </tr>
                <tr>
                    <td><label for="short">ShortName</label></td>
                    <td><input type="text" name="short" id="short" value="" class="text ui-widget-content ui-corner-all" style="width: 100%"></td>
                </tr>

                <!-- Allow form submission with keyboard without duplicating the dialog button -->
            </table>
            <div id="dialog-form-legalForm-save-message"></div>

            <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">

        </fieldset>
    </form>
</div>


<div id="dialog-form-bank" title="Редактирование (Bank) Банка" style="display: none">
    <p class="validateTips">Все поля должны быть заполнены.</p>

    <form>
        <fieldset>
            <table width="100%">
                <tr>
                    <td><label for="bank-id">IDBank</label></td>
                    <td><input type="text" name="bank-id" id="bank-id" value="" class="text ui-widget-content ui-corner-all" style="width: 100% "></td>
                </tr>
                <tr>
                    <td><label for="bank-name">Name</label></td>
                    <td><input type="text" name="bank-name" id="bank-name" value="" class="text ui-widget-content ui-corner-all" style="width: 100%"></td>
                </tr>
                <tr>
                    <td><label for="bank-address">Address</label></td>
                    <td><input type="text" name="bank-address" id="bank-address" value="" class="text ui-widget-content ui-corner-all" style="width: 100%"></td>
                </tr>

                <!-- Allow form submission with keyboard without duplicating the dialog button -->
            </table>
            <div id="dialog-form-bank-save-message"></div>

            <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">

        </fieldset>
    </form>
</div>

<div id="dialog-form-activity" title="Редактирование (Activity) GKED" style="display: none">
    <p class="validateTips">Все поля должны быть заполнены.</p>

    <form>
        <fieldset>
            <table width="100%">
                <tr>
                    <td><label for="activity-id-activity">IDActivity</label></td>
                    <td><input type="text" name="activity-id-activity" id="activity-id-activity" value="" class="text ui-widget-content ui-corner-all" style="width: 100%" readonly></td>
                </tr>
                <tr>
                    <td><label for="activity-activity-id">ActivityID</label></td>
                    <td><input type="text" name="activity-activity-id" id="activity-activity-id" value="" class="text ui-widget-content ui-corner-all" style="width: 100% "></td>
                </tr>
                <tr>
                    <td><label for="activity-name">Name</label></td>
                    <td><input type="text" name="activity-name" id="activity-name" value="" class="text ui-widget-content ui-corner-all" style="width: 100%"></td>
                </tr>
                <tr>
                    <td><label for="activity-gked">Gked</label></td>
                    <td><input type="text" name="activity-gked" id="activity-gked" value="" class="text ui-widget-content ui-corner-all" style="width: 100%"></td>
                </tr>


                <!-- Allow form submission with keyboard without duplicating the dialog button -->
            </table>
            <div id="dialog-form-activity-save-message"></div>
            <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">

        </fieldset>
    </form>
</div>

<div id="dialog-form-ChiefBasis" title="Редактирование (ChiefBasis) Осн. для занимаемой должности" style="display: none">
    <p class="validateTips">Все поля должны быть заполнены.</p>

    <form>
        <fieldset>
            <table width="100%">
                <tr>
                    <td><label for="chiefBasis-id">IDChiefBasis</label></td>
                    <td><input type="text" name="chiefBasis-id" id="chiefBasis-id" value="" class="text ui-widget-content ui-corner-all" style="width: 100%" readonly></td>
                </tr>
                <tr>
                    <td><label for="chiefBasis-name">ActivityID</label></td>
                    <td><input type="text" name="chiefBasis-name" id="chiefBasis-name" value="" class="text ui-widget-content ui-corner-all" style="width: 100% "></td>
                </tr>


                <!-- Allow form submission with keyboard without duplicating the dialog button -->
            </table>
            <div id="dialog-form-chiefBasis-save-message"></div>
            <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">

        </fieldset>
    </form>
</div>

<!--button id="create-user">Create new user</button-->

<div id="accordion">
    <h3>Форма собственности (LegalForm)</h3>
    <div class="resizable" >
        <table id="legalForm" class="display nowrap" style="width:100%;">
            <thead>
            <tr>
                <th>IDLegalForm</th>
                <th>OwnershipFormID</th>
                <th>Name</th>
                <th>ShortName</th>
                <th>Facet</th>
            </tr>
            </thead>
        </table>
    </div>
    <h3>Банки (Bank)</h3>
    <div class="resizable">
        <input type="button" id="add-bank-button" value="Добавить банк" style="">
        <table id="bank" class="display nowrap" style="width:100%;">
            <thead>
            <tr>
                <th>IDBank</th>
                <th>Name</th>
                <th>Address</th>
            </tr>
            </thead>
        </table>
    </div>
    <h3>ГКЕД (Activity)</h3>
    <div class="resizable">
        <table id="activity" class="display nowrap" style="width:100%;">
            <thead>
            <tr>
                <th>IDActivity</th>
                <th>ActivityID</th>
                <th>Name</th>
                <th>Gked</th>
            </tr>
            </thead>
        </table>
    </div>
    <h3>Основание для занимаемой должности (ChiefBasis)</h3>
    <div class="resizable">
        <table id="chiefBasis" class="display nowrap" style="width:100%;">
            <thead>
            <tr>
                <th>IDChiefBasisy</th>
                <th>Name</th>
            </tr>
            </thead>
        </table>
    </div>
</div>

