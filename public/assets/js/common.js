const commonJs = {
    sendAjax: function(opt, successCallback = null, beforeCallback = null, errorCallback = null) {
        $.ajax({
            url: opt.url,
            method: opt?.method ?? 'post',
            data: opt?.data ?? {},
            dataType: opt?.dataType ?? 'json',
            beforeSend: function(xhr, settings) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken);
                if (typeof beforeCallback === 'function') {
                    beforeCallback();
                }
            },
            success: function(res) {
                if (typeof successCallback === 'function') {
                    successCallback(res);
                }
            },
            error: function(xhr, e) {
                if (typeof errorCallback === 'function') {
                    errorCallback(e);
                }
                if (xhr.status === 401) {
                    window.location.replace('/');
                }
            }
        });
    },
    sendPromiseAjax: function(opt, beforeCallback = null) {
        return new Promise(async (resolve, reject) => {
            await commonJs.sendAjax(opt, resolve, beforeCallback, reject);
        });
    },
    isDuplicate: function(arr)  {
        return arr.some(function (x) {
            return arr.indexOf(x) !== arr.lastIndexOf(x);
        });
    },
    pwdValidate: function (password) {
        return /^.*(?=^.{6,16}$)(?=.*\d)(?=.*[a-zA-Z]).*$/.test(password);
        //return /^.*(?=^.{6,16}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!*@#$%^&+=_]).*$/.test(password);
    },
    getCheckboxCheckedValue: function(name) {
        const chkArr = [];
        $('input[name="'+name+'"]:checked').each(function() {
            chkArr.push($(this).val());
        });
        return chkArr;
    }
}

$(function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    $('#allCheck').on({
        click: function() {
            $('input[name="chk[]"]').prop('checked', $(this).prop('checked'));
        }
    })
});
