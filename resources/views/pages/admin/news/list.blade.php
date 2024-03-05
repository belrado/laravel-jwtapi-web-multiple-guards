@extends('layouts.default')

@section('pageTitle')
    <x-page-title :page-title="$pageTitle" />
@endsection

@section('content')
<style>
    .ui-datepicker{z-index: 9999 !important};
</style>
    {{--<a class="btn btn-primary" data-bs-toggle="modal" href="#defaultModal" role="button">Open first modal</a>--}}
    <div class="row mb-4">
        <div class="col-auto me-auto">
            <div class="btn-group" role="group" aria-label="Basic example">
                <button type="button" id="allUseSet" class="btn btn-outline-secondary btn-sm">사용 유무 설정</button>
                <button type="button" id="allServiceDateSet" class="btn btn-outline-secondary btn-sm">노출일 설정</button>
            </div>
        </div>
        <div class="col-auto">
            <a href="{{route('news.category')}}" class="btn btn-secondary btn-sm">카테고리 / 해시태그 관리</a>
            <a href="{{route('news.write')}}" class="btn btn-secondary btn-sm">뉴스 작성</a>
        </div>
    </div>

    <x-tab :tab-type="$categories" :tab-id="$categoryId" />

    <div class="">
        <table class="table mb-4">
            <thead>
            <tr>
                <th scope="col"><label><input class="form-check-input" type="checkbox" value="all" id="allCheck" /></label></th>
                <th scope="col">#</th>
                <th scope="col">카테고리</th>
                <th scope="col">제목 (Ko)</th>
                <th scope="col">사용 유무</th>
                <th scope="col">서비스 유무</th>
                <th scope="col">노출일</th>
                <th scope="col">작성일</th>
                <th scope="col">등록/수정</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <div id="paginationWrap"></div>
    </div>
@endsection

@section('appModal')
    <x-modal :modal-id="$detailModalId" :modal-class="$modalClass">
        <form class="news-detail" id="newsDetailForm">
            <input type="hidden" name="no" id="no" value="" />
            <input type="hidden" name="news_no" id="news_no" value="" />
            <div class="border-bottom pb-3 mb-2">
                <span>작성 / 마지막 수정한 관리자: </span>
                <strong id="updateAdmin"></strong>
            </div>
            <div class="row mb-2 align-items-center">
                <div class="col-auto me-auto ">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="news_use" id="news_use">
                        <label class="form-check-label" for="news_use">사용 유무</label>
                    </div>
                </div>
                <div class="col-auto">
                    <div>
                        <strong>작성일:</strong>
                        <span id="createdAt"></span>
                    </div>
                    <div>
                        <strong>수정일:</strong>
                        <span id="updatedAt"></span>
                    </div>
                </div>
            </div>
            <label class="input-group mb-3">
                <span class="input-group-text" style="min-width: 6em">노출일</span>
                <input type="text" name="service_date" class="form-control service_date" id="service_date" />
            </label>
            <label class="input-group mb-3">
                <span class="input-group-text" style="min-width: 6em">카테고리</span>
                <select class="form-select" name="cate_no" id="cate_no">
                    @foreach ($categories as $val)
                        <option value="{{$val->cate_no}}">@if($val->use === 'n') [사용중지] @endif{{$val->cate_ko}}</option>
                    @endforeach
                </select>
            </label>
            <label class="input-group mb-3">
                <span class="input-group-text" style="min-width: 6em">제목 (Ko)</span>
                <input type="text" name="subject_ko" placeholder="required" class="form-control" />
            </label>
            <label class="input-group mb-3">
                <span class="input-group-text" style="min-width: 6em">제목 (En)</span>
                <input type="text" name="subject_en" placeholder="required" class="form-control">
            </label>
            <label class="input-group mb-3">
                <span class="input-group-text" style="min-width: 6em">본문 (Ko)</span>
                <textarea class="form-control" name="contents_ko" placeholder="required" style="min-height: 150px"></textarea>
            </label>
            <label class="input-group mb-3">
                <span class="input-group-text" style="min-width: 6em">본문 (En)</span>
                <textarea class="form-control" name="contents_en" placeholder="required" style="min-height: 150px"></textarea>
            </label>
            <div class="tag-wrap input-group">
                <span class="input-group-text" style="min-width: 6em">태그 선택</span>
                <div class="form-control">
                    @foreach ($tags as $val)
                        <div style="display: inline-block" class="mb-1">
                            <input type="checkbox" class="btn-check" name="tags[]" value="{{$val->tag_no}}" id="tag_{{$val->tag_no}}" autocomplete="off">
                            <label class="btn btn-outline-warning btn-sm" for="tag_{{$val->tag_no}}">{{$val->tag_ko}}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </form>
    </x-modal>
    <x-modal :modal-id="$allUseSetModalId">
        <div class="col-auto me-auto ">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="news_use" id="all_news_use">
                <label class="form-check-label" for="all_news_use">사용 유무</label>
            </div>
        </div>
    </x-modal>
    <x-modal :modal-id="$allServiceDateSetModalId">
        <label class="input-group mb-3">
            <span class="input-group-text" style="min-width: 6em">노출일</span>
            <input type="text" name="all_service_date" class="form-control service_date" id="all_service_date" />
        </label>
    </x-modal>
@endsection

@section('script')
    <script>
        const listInfo = {
            page: {{$page}},
            category: 'all',
            limit: {{$limit}}
        };

        const detailInfo = {
            detail: {},
            tags: [],
        };

        const listHtml = function(data, pageTotal) {
            let html = '';
            data.forEach((val, index) => {
                html += `
                        <tr style="cursor: pointer; ${val.news_use === 'n' && 'color: #ccc'}"
                            ${val.news_use === 'n' && 'class="k-tooltip" data-bs-toggle="tooltip" data-bs-placement="left" title="뉴스 노출 중지"'} data-news-no="${val.news_no}">
                            <th scope="row"><label><input name="chk[]" class="form-check-input" type="checkbox" value="${val.news_no}" /></label></th>
                            <th scope="row" class="list-row">${pageTotal - index}</th>
                            <td class="list-row">
                            ${val.cate_use === 'y'
                            ? `<span class="badge bg-secondary">${val.cate_ko}</span>`
                            : `<span class="k-tooltip badge bg-light text-dark" data-bs-toggle="tooltip" data-bs-placement="left" ${val.cate_use !== null ? 'title="카테고리 사용 안함"' : 'title="삭제된 카테고리"'}>${val.cate_use !== null ? val.cate_ko : '삭제'}</span>`}
                            </td>
                            <td class="list-row">${val.subject_ko}</td>
                            <td class="list-row">${val.news_use}</td>
                            <td class="list-row">${val.service}</td>
                            <td class="list-row">${val.service_date}</td>
                            <td class="list-row">${val.news_created_at}</td>
                            <td class="list-row">${val.update_admin}</td>
                        </tr>
                        `
            });

            return html;
        };
        const getListAjax = function(page, cate_no = 'all') {
            const opt = {
                url: '{{route('news.ajax.get.list')}}',
                method: 'get',
                data: {
                    page: page,
                    type: 'newsCategories',
                    mode: 'web',
                    limit: listInfo.limit,
                }
            }
            if (cate_no !== 'all') {
                opt.data.cate_no = cate_no;
            }
            const tableBody = $('table tbody');

            commonJs.sendAjax(opt, function(response) {
                if (response?.resultCode === '0000') {
                    $('#allCheck').prop('checked', false);
                    tableBody.empty().append(listHtml(response.data, response.pageTotal))
                    $('#paginationWrap').empty().append(response.paginationHtml)

                    tableBody.find(".k-tooltip").each(function(i) {
                        new bootstrap.Tooltip(tableBody.find(".k-tooltip")[i])
                    });

                } else {
                    tableBody.append('<tr><td colspan="9">'+response.message+'</td></tr>');
                }
            }, null, function() {
                tableBody.append('<tr><td colspan="9">네트워크 오류로 데이터를 불러오지 못했습니다.</td></tr>');
            });
        }
        $(function() {
            const modalObj = new bootstrap.Modal(document.getElementById('detailModal'), {
                keyboard: false
            });

            const modalObj2 = new bootstrap.Modal(document.getElementById('allUseSetModalId'), {
                keyboard: false
            });

            const modalObj3 = new bootstrap.Modal(document.getElementById('allServiceDateSetModalId'), {
                keyboard: false
            });

            $('.service_date').datepicker({
                dateFormat: 'yy-mm-dd'
                ,minDate: 1
                ,yearSuffix: "년" //달력의 년도 부분 뒤 텍스트
                ,monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'] //달력의 월 부분 텍스트
                ,monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'] //달력의 월 부분 Tooltip
                ,dayNamesMin: ['일','월','화','수','목','금','토'] //달력의 요일 텍스트
                ,dayNames: ['일요일','월요일','화요일','수요일','목요일','금요일','토요일'] //달력의 요일 Tooltip
            });

            $(document).on('click', '.btn-page-number', function(e) {
                e.preventDefault();
                listInfo.page = $(this).data('pageNumber');
                getListAjax(listInfo.page, listInfo.category);
            });

            $('#categoryTab').find('a').on({
                click: function(e) {
                    e.preventDefault();
                    $('#categoryTab').find('a').removeClass('active');
                    $(this).addClass('active');
                    listInfo.category = $(this).data('cateNo');
                    getListAjax(1, listInfo.category);
                }
            });

            $(document).on('click', '.list-row', function(e) {
                const newsNo = $(this).closest('tr').data('newsNo');
                const newsDetail = $('#newsDetailForm');
                newsDetail.find('input[type="text"], textarea').val('')
                newsDetail.find('input[type="checkbox"]').prop('checked', false);
                detailInfo.detail = {};
                detailInfo.tags = [];
                const opt = {
                    url: '{{route('news.ajax.get.detail')}}',
                    method: 'get',
                    data: {
                        news_no: newsNo,
                    }
                };

                newsDetail.find('#cate_no').find('#delCate').remove();

                commonJs.sendAjax(opt, function(response) {
                    console.log('response', response)
                    if (response?.resultCode === '0000' && response?.data?.detail) {
                        modalObj.show();
                        const detail = response.data.detail;
                        $('#detailModalLabel').html('[뉴스 상세보기] <span class="text-truncate" style="max-width: 300px">' + response.data.detail.subject_ko + '</sapn>');
                        detailInfo.detail = response.data.detail;
                        detailInfo.detail.service_date = response.data.detail.service_date ?? '';
                        newsDetail.find('#no').val(detailInfo.detail.no);
                        newsDetail.find('#news_no').val(detailInfo.detail.news_no);
                        newsDetail.find('#news_use').prop('checked', detail.news_use === 'y');
                        newsDetail.find('#updateAdmin').text(detail.update_admin)
                        newsDetail.find('#createdAt').text(detail.news_created_at);
                        newsDetail.find('#updatedAt').text(detail.news_updated_at);
                        if (!detail.cate_ko) {
                            newsDetail.find('#cate_no').append('<option value="" id="delCate" selected>삭제된 카테고리</option>')
                        } else {
                            newsDetail.find('#cate_no').val(detail.cate_no).prop("selected", true);
                        }
                        newsDetail.find('input[type="text"], textarea').each(function() {
                            $(this).val(detail[$(this).attr('name')]);
                        });
                        if (response.data?.tags) {
                            response.data.tags.forEach(v => {
                                detailInfo.tags.push(v);
                                newsDetail.find('#tag_'+v.tag_no).prop('checked', true);
                            });
                        }
                    } else {
                        alert(response.message);
                    }

                }, null, function(e) {
                    alert(e.message);
                });
            });

            $('#detailModalSubmit').on({
                click: function() {
                    const newsDetail = $('#newsDetailForm');
                    let diffCheck = false;
                    let requiredCheck = false;
                    const sendData = {};
                    if (detailInfo.detail.news_use !== (newsDetail.find('#news_use').prop('checked') ? 'y' : 'n')) {
                        diffCheck = true;
                    }
                    newsDetail.find('input[type="text"], textarea, input[type="hidden"]').each(function() {
                        if (detailInfo.detail[$(this).attr('name')].toString() !== $(this).val()) {
                            diffCheck = true;
                        }
                        if ($.trim($(this).val()) === '') {
                            $(this).focus()
                            requiredCheck = true;
                            return false;
                        }
                        sendData[$(this).attr('name')] = $(this).val();
                    });

                    const tagsCheckedNum = newsDetail.find('input[name="tags[]"]:checked').length
                    if (tagsCheckedNum !== detailInfo.tags.length) {
                        diffCheck = true;
                    }

                    if (requiredCheck) {
                        alert('필수 항목 누락');
                        return false;
                    }

                    if (!diffCheck) {
                        alert('수정할 사항이 없습니다.');
                        return false;
                    }

                    const oldTags = detailInfo.tags.map(v => v.tag_no);
                    const newTags = [];
                    newsDetail.find('input[name="tags[]"]:checked').each(function() {
                        newTags.push(parseInt($(this).val()));
                    })

                    const deleteTags = oldTags.filter(x => !newTags.includes(x));
                    const insertTags = newTags.filter(x => !oldTags.includes(x));

                    sendData.use = newsDetail.find('#news_use').prop('checked') ? 'y' : 'n';
                    sendData.cate_no = newsDetail.find('#cate_no').val();
                    if (sendData.cate_no === '' || !sendData.cate_no) {
                        alert('카테고리를 선택해 주세요.');
                        newsDetail.find('#cate_no').focus();
                        return false;
                    }
                    sendData.insert_tag = insertTags;
                    sendData.delete_tag = deleteTags;

                    const opt = {
                        url: '{{route('news.ajax.put.detail')}}',
                        method: 'put',
                        data: sendData
                    };

                    commonJs.sendAjax(opt, function(response) {
                        if (response?.resultCode === '0000') {
                            alert('데이터 수정 완료');
                            modalObj.hide();
                            getListAjax(listInfo.page, listInfo.category);
                        } else {
                            alert(response.message);
                        }
                    });
                }
            });

            $('#allUseSet, #allServiceDateSet').on({
                click: function() {
                    const chkNum = $('input[name="chk[]"]:checked').length;
                    if (chkNum > 0) {
                        const eventId = $(this).attr('id');
                        if (eventId === 'allUseSet') {
                            $('#allUseSetModalIdLabel').text('선택된 뉴스 사용 유무 설정')
                            modalObj2.show();
                        } else if (eventId === 'allServiceDateSet'){
                            $('#allServiceDateSetModalIdLabel').text('선택된 뉴스 노출일 설정')
                            modalObj3.show();
                        } else {
                            return false;
                        }
                    } else {
                        alert('선택된 항목이 없습니다.')
                    }
                }
            });

            $('#allUseSetModalIdSubmit, #allServiceDateSetModalIdSubmit').on({
                click: function() {
                    const chkArr = commonJs.getCheckboxCheckedValue('chk[]');
                    const eventId = $(this).attr('id');
                    let opt = {
                        method: 'put',
                        data: {
                            news_ids: chkArr
                        }
                    }
                    if (eventId === 'allUseSetModalIdSubmit') {
                        modalObj2.show();
                        opt.url = '{{route('news.ajax.put.allUse')}}';
                        opt.data.use = $('#all_news_use').prop('checked') ? 'y' : 'n';
                    } else if (eventId === 'allServiceDateSetModalIdSubmit') {
                        modalObj3.show();
                        opt.url = '{{route('news.ajax.put.allServiceDate')}}';
                        opt.data.service_date = $('#all_service_date').val();
                        if ($.trim(opt.data.service_date) === '') {
                            alert('노출일을 입력해 주세요');
                            $('#all_service_date').focus();
                            return false;
                        }
                    } else {
                        return false;
                    }

                    commonJs.sendAjax(opt, function(response) {
                        console.log(response);
                        if(response?.resultCode === '0000') {
                            alert('데이터 일괄 수정 완료');
                            if (eventId === 'allUseSetModalIdSubmit') {
                                modalObj2.hide();
                            } else if (eventId === 'allServiceDateSetModalIdSubmit') {
                                modalObj3.hide();
                            }
                            getListAjax(listInfo.page, listInfo.category);
                        } else {
                            alert(response.message);
                        }
                    });
                }
            });


            getListAjax(listInfo.page, listInfo.category);
        })
    </script>
@endsection
