@extends('layouts.default')

@section('pageTitle')
    <x-page-title :page-title="$pageTitle" />
@endsection

@section('content')
    <div class="d-flex justify-content-md-center align-items-center">
        <div class="card col-sm-6 mb-5">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <span class="badge bg-warning">{{$myInfo['type']}}</span>
                    <span class="badge bg-warning">Lv.{{$myInfo['level']}}</span>
                    <h5 class="m-0 mt-1">{{$myInfo['email']}}</h5>
                </li>
                <li class="list-group-item p-0">
                    <div class="card-header">
                        이름
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{$myInfo['name']}}</p>
                    </div>
                </li>
                <li class="list-group-item p-0">
                    <div class="card-header">
                        닉네임
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{$myInfo['nick_name']}}</p>
                    </div>
                </li>
                <li class="list-group-item p-0">
                    <div class="card-header">
                        변경 사항
                    </div>
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modifyType" id="flexRadioDefault1" value="password">
                            <label class="form-check-label" for="flexRadioDefault1">
                                비밀번호 변경
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modifyType" id="flexRadioDefault2" checked value="info">
                            <label class="form-check-label" for="flexRadioDefault2">
                                정보 변경
                            </label>
                        </div>
                    </div>
                </li>
            </ul>
            <button id="btn-update-info" class="btn btn-secondary" style="border-radius: 0">회원정보 변경</button>
        </div>
    </div>
@endsection

@section('appModal')
    <x-modal>
        <div>
            <p>비밀번호를 입력해 주세요.</p>
            <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword">Password</label>
            </div>
        </div>
    </x-modal>
@endsection

@section('script')
    <script>
        $(async function() {
            const modalObj = new bootstrap.Modal(document.getElementById('defaultModal'), {
                keyboard: false
            });

            $('#btn-update-info').on({
                click: function() {
                    modalObj.show();
                    $('#defaultModalLabel').text('정보변경');
                }
            });

            $('#defaultModalSubmit').on({
                click: async function() {
                    try {
                        const password = $('#floatingPassword').val();
                        const modifyType = $('input[name="modifyType"]:checked').val();
                        const sendData = {
                            password: password,
                            modifyType: modifyType
                        }
                        const opt = {
                            url: '{{route('auth.check')}}',
                            data: sendData
                        };
                        const response = await commonJs.sendPromiseAjax(opt);
                        if (response?.resultCode === '0000') {
                            window.location.replace('{{route('update.profile')}}');
                        } else {
                            alert(response.message);
                            //modalObj.hide();
                        }
                    } catch (e) {
                        alert('네트워크 에러 잠시 후 다시시도해주세요.');
                        modalObj.hide();
                    }
                }
            });
        });
    </script>
@endsection

