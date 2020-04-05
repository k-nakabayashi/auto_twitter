// モーダルの開閉をします。
$(".js-Modal__btn--register").on("click", function () {
  $(".js-Modal__cover").fadeIn();
  $(".c-modal-Form.login ").fadeOut();
  $(".c-modal-Form.repass").fadeOut();

  $(".c-modal-Form.register ").toggle();
});

$(".js-Modal__btn--login").on("click", function () {
  $(".js-Modal__cover").fadeIn();
  $(".c-modal-Form.register ").fadeOut();
  $(".c-modal-Form.repass").fadeOut();

  $(".c-modal-Form.login ").toggle();
});

$(".js-Modal__btn--repass").on("click", function () {
  $(".js-Modal__cover").fadeIn();
  $(".c-modal-Form.register ").fadeOut();
  $(".c-modal-Form.login").fadeOut();

  $(".c-modal-Form.repass").toggle();
});


$(".js-Modal__close").on("click", function () {
  $(".c-modal-Form").fadeOut();
  $(".js-Modal__cover").fadeOut();
  $(".js-Modal__cover2").fadeOut();
  $('.c-Main-Menus').removeClass('is-Opened');
});

