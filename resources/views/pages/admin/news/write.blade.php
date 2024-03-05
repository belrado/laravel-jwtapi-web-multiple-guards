@extends('layouts.default')

@section('pageTitle')
<x-page-title :page-title="$pageTitle" />
@endsection

@section('content')
<div>
    <div class="row mb-4">
        <div class="col-auto me-auto">
            <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-outline-secondary btn-sm" disabled style="color:#000; font-weight: bold">입력폼 <span id="form-num">1</span></button>
                <button type="button" id="btn-create" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="입력폼 추가">&nbsp;<i data-feather="plus-circle" ></i>&nbsp;</button>
                <button type="button" id="btn-submit" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="뉴스 저장">&nbsp;<i data-feather="check-circle" ></i>&nbsp;</button>
                <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="입력폼 전체 삭제">&nbsp;<i data-feather="x-circle" ></i>&nbsp;</button>
            </div>
        </div>
        <div class="col-auto">
            <a href="{{route('news.category')}}" class="btn btn-secondary btn-sm">카테고리 / 해시태그 관리</a>
            <a href="{{route('news.list')}}" class="btn btn-secondary btn-sm">뉴스 목록</a>
        </div>
    </div>

    <div id="form-box-wrap">
        <article class="card mb-3 shadow-sm">
            <header class="card-header">
                <div class="row">
                    <strong class="col-auto me-auto">뉴스 작성</strong>
                    <div class="col-auto">
                        <button type="button" class="btn-form-close btn-close btn-sm" aria-label="Close" id="btn"></button>
                    </div>
                </div>
            </header>
            <div class="card-body">
                <label class="input-group mb-3">
                    <span class="input-group-text" style="min-width: 6em">카테고리</span>
                    <select class="form-select" name="cate_no[]" aria-label="">
                        @foreach ($categories as $val)
                            <option value="{{$val->cate_no}}">{{$val->cate_ko}}</option>
                        @endforeach
                    </select>
                </label>
                <label class="input-group mb-3">
                    <span class="input-group-text" style="min-width: 6em">제목 (Ko)</span>
                    <input type="text" name="subject_ko[]" placeholder="required" class="form-control" />
                </label>
                <label class="input-group mb-3">
                    <span class="input-group-text" style="min-width: 6em">제목 (En)</span>
                    <input type="text" name="subject_en[]" placeholder="required" class="form-control">
                </label>
                <label class="input-group mb-3">
                    <span class="input-group-text" style="min-width: 6em">본문 (Ko)</span>
                    <textarea class="form-control" name="contents_ko[]" placeholder="required" style="min-height: 150px"></textarea>
                </label>
                <label class="input-group mb-3">
                    <span class="input-group-text" style="min-width: 6em">본문 (En)</span>
                    <textarea class="form-control" name="contents_en[]" placeholder="required" style="min-height: 150px"></textarea>
                </label>
                <div class="tag-wrap input-group">
                    <span class="input-group-text" style="min-width: 6em">태그 선택</span>
                    <div class="form-control">
                        @foreach ($tags as $val)
                            <div style="display: inline-block" class="mb-1">
                                <input type="checkbox" class="btn-check" name="tags[]" value="{{$val->tag_no}}" id="tag_{{$val->tag_ko}}_1" autocomplete="off">
                                <label class="btn btn-outline-warning btn-sm" for="tag_{{$val->tag_ko}}_1">{{$val->tag_ko}}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function() {
        const formWrap = $('#form-box-wrap');
        const getFormLength = function() {
            return formWrap.find('.card').length;
        }
        const setFormLength = function() {
            $('#form-num').text(getFormLength());
        }

        $('#btn-create').on({
            click: function() {
                if (getFormLength() < 20) {
                    const form = formWrap.find('.card:first').clone();
                    form.find('input[type="text"], textarea').val('');
                    form.find('input[type="checkbox"]').prop('checked', false);
                    formWrap.append(form);
                    setFormLength();
                    formWrap.find('.tag-wrap').each(function(i) {
                        $(this).find('label').each(function() {
                            $(this).attr('for', $(this).attr('for').replace(/[0-9]$/, (i+1)))
                        });
                        $(this).find('input').each(function() {
                            $(this).attr('id', $(this).attr('id').replace(/[0-9]$/, (i+1)))
                        });
                    });
                } else {
                    alert('게시물은 한번에 20개 까지만 작성 가능합니다.')
                }
            }
        });

        $('#btn-reset').on({
            click: function() {
                formWrap.find('.card:gt(0)').remove();
                setFormLength();
            }
        });

        formWrap.on('click', '.btn-form-close', function() {
            if (formWrap.find('.card').length > 1) {
                $(this).closest('.card').remove();
                setFormLength();
            }
        });

        $('#btn-submit').on({
            click: async function() {
                let check = true;
                formWrap.find('input[type="text"], textarea').each(function() {
                    if ($.trim($(this).val()) === '') {
                        check = false;
                        $(this).focus();
                        alert('필수 입력 항목 누락');
                        return false;
                    }
                });

                if (check) {
                    if (confirm('작성한 ' + getFormLength()+' 개의 게시물을 등록하시겠습니까?')) {
                        const sendData = [];
                        formWrap.find('.card').each(function() {
                            const row = {}
                            row.tags = [];
                            row.cate_no = $(this).find('select').val();
                            $(this).find('input[type="text"], textarea').each(function() {
                                row[($(this).attr('name')).replace('[]', '')] = $(this).val();
                            })
                            $(this).find('input[type="checkbox"]:checked').each(function() {
                                row.tags.push($(this).val());
                            });
                            sendData.push(row)
                        });

                        try {
                            const opt = {
                                url: '{{route('news.ajax.post.write')}}',
                                data: {...sendData}
                            };
                            const response = await commonJs.sendPromiseAjax(opt);
                            if (response?.resultCode === '0000') {
                                $('#btn-reset').trigger('click');
                                const form = formWrap.find('.card:first');
                                form.find('input[type="text"], textarea').val('');
                                form.find('input[type="checkbox"]').prop('checked', false);
                                alert('저장 완료');
                            } else {
                                alert(response.message);
                            }

                        } catch (e) {
                            console.log('error', e)
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
