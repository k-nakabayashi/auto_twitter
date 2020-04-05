require('./bootstrap');

function getAxios(form) {

window.axios.get('/RestApi/show', {
  params: {
    column: 'email',
    value: form.email.value,
    model_name: "Tw_Account",
  },
}).then(res => {

  var data = res.data;

  if (Object.keys(data.errors).length > 0) {
    var error_messaage = "";
    for (var i = 0; i < Object.keys(data.errors).length; i ++) {
      error_messaage += data.errors[i];
    }
    alert(error_messaage);
    alert("アカウント取得に失敗しました。下記が原因です。\n" + error_messaage);

    return false;
  }
  return  true; 
})
.catch(err => {
  console.log(err);
  return false;
}).finally(() => {
  alert(33);
  console.log('finally')
});

}