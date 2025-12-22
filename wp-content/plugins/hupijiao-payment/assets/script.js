function submit_form(event){

            event.preventDefault();
         
            let myform=new FormData(document.getElementById('myform'));
            myform.append('hupijiao_nonce',pay_ajax_obj.nonce);
            fetch(pay_ajax_obj.ajax_url,{
                method:'POST',
                body:myform,
            }).then(response=>{response.json();}).then(data=>{console.log(data);}).catch(err=>{console.log(err);});
            }
