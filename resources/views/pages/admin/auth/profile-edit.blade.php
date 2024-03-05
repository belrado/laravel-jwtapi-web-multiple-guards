@extends('layouts.default')

@section('pageTitle')
    <x-page-title :page-title="$pageTitle" />
@endsection

@section('content')
    <div class="d-flex justify-content-md-center">
        <div class="border col-sm-6 p-2">
            <div class="card mb-2">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <span class="badge bg-warning">{{$myInfo['type']}}</span>
                        <span class="badge bg-warning">Lv.{{$myInfo['level']}}</span>
                        <h5 class="m-0 mt-1">{{$myInfo['email']}}</h5>
                    </li>
                    @if ($onceAuth === 'password')
                        <li class="list-group-item">
                            <p class="m-0 mb-1 text-danger lh-1" style="font-size:13px">* 비밀번호 변경시 기존 비밀번호, 새비밀번호, 새비밀번호확인 세칸을 입력하세요</p>
                            <p class="m-0 text-danger lh-1" style="font-size:13px">* 비밀번호는 영문 숫자 포함 6~16자 로 작성해주세요.</p>
                        </li>
                        <li class="list-group-item">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" placeholder="Password">
                                <label for="password">Password</label>
                            </div>
                        </li>
                        <li class="list-group-item">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="newPassword" placeholder="New Password">
                                <label for="newPassword">New Password</label>
                            </div>
                        </li>
                        <li class="list-group-item pb-4">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password">
                                <label for="confirmPassword">Confirm Password</label>
                            </div>
                        </li>
                    @else
                        <li class="list-group-item">
                            <p class="mb-0 text-danger lh-1 mb-1" style="font-size:13px">* 추후 닉네임 변경은 정책에 의해 정해진 기간이 지나야 변경될 수 있음</p>
                            <p class="mb-0 text-danger lh-1" style="font-size:13px">* 닉네임은 2글자 이상 작성해 주세요</p>
                        </li>
                        <li class="list-group-item pb-4">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nickName" placeholder="NickName" value="{{$myInfo['nick_name']}}">
                                <label for="nickName">Nickname</label>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
            <div class="d-flex">
                <button id="btn-update-info" class="btn btn-secondary flex-grow-1">회원정보 변경</button>
            </div>

        </div>

    </div>
@endsection

@section('script')
    <script>
        $(function() {
            $('#btn-update-info').on({
                click: async function() {
                    try {
                        @if ($onceAuth === 'password')
                        const password = $('#password').val();
                        const newPassword = $('#newPassword').val();
                        const cPassword = $('#confirmPassword').val();
                        if (password === '' || newPassword === '' || cPassword === '') {
                            alert('빈칸을 입력해 주세요.');
                            return false;
                        }

                        if (password === newPassword) {
                            alert('변경할 비밀번호가 예전과 동일합니다.')
                            return false;
                        }

                        if (newPassword !== cPassword) {
                            alert('새로운 비밀번호와 새로운 비밀번호 확인이 일치 하지 않습니다.');
                            return false;
                        }

                        if (!commonJs.pwdValidate(newPassword)) {
                            alert('비밀번호는 영문 숫자 포함 6~16자 로 작성해주세요.')
                            return false;
                        }

                        const opt = {
                            url: '{{route('update.password')}}',
                            method: 'put',
                            data: {
                                password: password,
                                new_password: newPassword,
                                c_password: cPassword,
                            }
                        }
                        const response = await commonJs.sendPromiseAjax(opt);
                        @else
                        const nickName = $.trim($('#nickName').val());
                        const oldNick = '{{$myInfo['nick_name']}}';

                        if (nickName === oldNick) {
                            alert('닉네임이 변경전 닉네임과 동일합니다.')
                            return false;
                        }

                        if (!/^.{2,}$/.test(nickName)) {
                            alert('닉네임은 2글자 이상입니다..')
                            return false;
                        }

                        const opt = {
                            url: '{{route('update.nickname')}}',
                            method: 'put',
                            data: {
                                nick_name: nickName,
                            }
                        };

                        const response = await commonJs.sendPromiseAjax(opt);
                        console.log('response');
                        @endif

                        if (response?.resultCode === '0000') {
                            alert('변경완료');
                            window.location.replace('{{route('login')}}');
                        } else {
                            alert(response.message);
                        }
                    } catch (e) {
                        alert('네트워크 에러 잠시 후 다시시도해주세요.');
                    }
                }
            });
        });
    </script>
@endsection
