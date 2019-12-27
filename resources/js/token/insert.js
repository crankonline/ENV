function insertSerial(name) {

  window.writeSerialNumber()
    .then(function () {
      $("[name='" + name + "']").val(window.tokenNumber || '');
    });

}