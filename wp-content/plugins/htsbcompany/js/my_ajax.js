/*
jQuery(document).ready(function($){
    $('#my-form').on('submit',function(e){
        e.preventDefault();
        var formData={
            action:my_ajax_obj.action,
            security:my_ajax_obj.nonce,
            data:$('#data-field').val()
        }
        $.ajax(
            {url: my_ajax_obj.ajax_url,
            type: 'POST',
            data:formData,
            beforeSend:function(){$('#submit-btn').prop('disabled',true)},
            success:function(response){
                if (response.success){$('#result').html(response.data.message);}
                else {$('#result').html(response.data)}
            },
            error:function(xhr,status,error){$('#result').html(error)},
            complete:function(){$('#submit-btn').prop('disabled',false)}
            }
        )
    })
})
*/


function submitViaRestAPI(){
    const data={data:document.getElementById('data-field').value}
    fetch('/wp-json/myplugin/v1/submit',{
        method:'POST',
        //credentials:'same-origin',
        headers:{
            'Content-Type':'application/json',
            //'_wpnonce':my_ajax_obj.nonce,
        },
		//_wpnonce:my_ajax_obj.nonce,
        body:JSON.stringify(data),
    })
    .then(response=>response.json())
    .then(data=>{
        console.log('success:',data)
    })
    .catch(error=>{
        console.error('Error:',error)
    })
}


jQuery(document).ready(function($){
    $('#my-form').on('submit',function(e){
        e.preventDefault();
        submitViaRestAPI();
    })
})
