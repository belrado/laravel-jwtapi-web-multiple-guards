@extends('layouts.default')

@section('pageTitle')
    <x-page-title :page-title="$pageTitle" />
@endsection

@section('content')
    <div id="classification-wrapper">
        <div class="row mb-4">
            <div class="col-auto me-auto">
            </div>
            <div class="col-auto">
                <a href="{{route('news.list')}}" class="btn btn-secondary btn-sm">뉴스 목록</a>
                <a href="{{route('news.write')}}" class="btn btn-secondary btn-sm">뉴스 작성</a>
            </div>
        </div>
        <div class="card mb-5 shadow-sm">
            <h6 class="card-header">카테고리</h6>
            <div class="card-body">
                <div class="mb-3">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled style="color:#000; font-weight: bold">카테고리 생성</button>
                        <button type="button" id="btn-cate-create" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="추가">&nbsp;<i data-feather="plus-circle" ></i>&nbsp;</button>
                        <button type="button" id="btn-cate-submit" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="저장">&nbsp;<i data-feather="check-circle" ></i>&nbsp;</button>
                    </div>
                </div>
                <div class="d-flex mb-3" id="cate-create-wrap"></div>
                {{-- 카테고리 목록 / 수정 --}}
                <p class="mb-3">카테고리 목록</p>
                <div class="mb-2" id="cate-list-wrap"></div>
            </div>
        </div>

        <div class="card shadow-sm">
            <h6 class="card-header">해시태그</h6>
            <div class="card-body">
                <div class="mb-3">
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled style="color:#000; font-weight: bold">해시태그 생성</button>
                        <button type="button" id="btn-tag-create" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="추가">&nbsp;<i data-feather="plus-circle" ></i>&nbsp;</button>
                        <button type="button" id="btn-tag-submit" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="저장">&nbsp;<i data-feather="check-circle" ></i>&nbsp;</button>
                    </div>
                </div>
                <div class="d-flex mb-3" id="tag-create-wrap"></div>
                {{-- 카테고리 목록 / 수정 --}}
                <p class="mb-3">태그 목록</p>
                <div class="mb-2" id="tag-list-wrap"></div>
            </div>
        </div>
    </div>
@endsection

@section('appModal')
    <x-modal>
        <div>
            <input type="hidden" name="no" value="" />
            <input type="hidden" name="type" value="" />
            <p id="updateAdmin"></p>

            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="use" id="modal_use">
                <label class="form-check-label" for="modal_tag_use">사용 유무</label>
            </div>

            <div class="input-group mb-3">
                <label class="input-group-text" for="modal_ko" style="width: 6em">tag (kor)</label>
                <input type="text" name="modal_ko" class="form-control" id="modal_ko" aria-describedby="basic-addon3">
            </div>

            <div class="input-group mb-3">
                <label class="input-group-text" for="modal_en" style="width: 6em">tag (eng)</label>
                <input type="text" name="modal_en" class="form-control" id="modal_en" aria-describedby="basic-addon3">
            </div>
        </div>
    </x-modal>
@endsection

@section('script')
    <script>
        const Classification = function() {
            this.title = '';
            this.classificationData = [];
            this.listUrl = null;
            this.listWrap = null;
            this.createwrap = null;
            this.createBtn = null;
            this.createSubmitBtn = null;
            this.type = '';
        }

        Classification.prototype = {
            getClassificationData: function(){
                return this.classificationData;
            },
            setDataListHtml: function(arrData) {
                this.classificationData;
                const wrap = this.listWrap;
                const html = (data) => (`<button type="button" class="btn-${this.type}-list btn ${data.use === 'n' ? 'btn-light' : 'btn-outline-secondary'} me-1 mb-1" data-bs-toggle="modal" data-bs-target="#defaultModal" data-no="${data[this.type+ '_no']}">
                                            ${data[this.type +'_ko']} | ${data[this.type +'_en']}
                                        </button>`);
                wrap.empty();
                this.classificationData.splice(0, this.classificationData.length);
                arrData.forEach(data => {
                    this.classificationData.push(data);
                    wrap.append(html(data));
                });
            },
            insertBoxHtml: function(index) {
                return `
                    <div class="cate-create-box card p-2 m-1">
                        <div class="d-flex flex-row-reverse mb-2"><button type="button" class="btn-${this.type}-insert-close btn-close btn-sm"></button></div>
                        <div class="input-tag-group input-group mb-2">
                            <label class="input-group-text" for="inert-${this.type}-ko${index}">Ko</label>
                            <input type="text" name="${this.type}_ko[]" class="form-control" id="inert-${this.type}-ko${index}" placeholder="required">
                        </div>
                        <div class="input-tag-group input-group mb-2">
                            <label class="input-group-text" for="inert-${this.type}-en${index}">En</label>
                            <input type="text" name="${this.type}_en[]" class="form-control" id="inert-${this.type}-en${index}" placeholder="required">
                        </div>
                    </div>`;
            },
            insertAction: function() {
                const _this = this;
                // 카테고리 & 해시태그 생성
                _this.createBtn.on({
                    click: function() {
                        console.log('craeate', _this.type)
                        const len = _this.createwrap.find('.cate-create-box').length
                        if (len < 6) {
                            _this.createwrap.append(_this.insertBoxHtml(len + 1));
                        } else {
                            alert('한번에 6개 이상 생성할 수 없습니다.')
                        }
                    }
                });
                $('#classification-wrapper').on('click', '.btn-'+_this.type+'-insert-close', function() {
                    $(this).closest('.cate-create-box').remove();
                    _this.createwrap.find('.cate-create-box').each(function(i) {
                        $(this).find('label').each(function() {
                            $(this).attr('for', $(this).attr('for').replace(/[0-9]$/, (i+1)))
                        });
                        $(this).find('input').each(function() {
                            $(this).attr('id', $(this).attr('id').replace(/[0-9]$/, (i+1)))
                        });
                    });
                });
                _this.createSubmitBtn.on({
                    click: async function() {
                        const len = _this.createwrap.find('.cate-create-box').length;
                        if (len <= 0) {
                            alert('생성된 카테고리가 없습니다.');
                        } else {
                            let check = true;
                            const arrKo = [];
                            const arrEn = [];

                            _this.createwrap.find('.cate-create-box').each(function() {
                                const valKo = $.trim($(this).find('input[name="'+_this.type+'_ko[]"]').val()); //.replace(/\s+/, '');
                                const valEn = $.trim($(this).find('input[name="'+_this.type+'_en[]"]').val()); //.replace(/\s+/, '');
                                if (/.{2}/.test(valKo)) {
                                    arrKo.push(valKo);
                                } else {
                                    check = false;
                                    arrKo.push(false);
                                }
                                if (/.{2}/.test(valEn)) {
                                    arrEn.push(valEn);
                                } else {
                                    check = false;
                                    arrEn.push(false);
                                }
                            });

                            if (commonJs.isDuplicate(arrKo) || commonJs.isDuplicate(arrEn)) {
                                check = false;
                            }

                            if (!check) {
                                alert('입력값이 없거나 중복되었습니다.');
                                return false;
                            }

                            const sendData = arrKo.map((v, i) => {
                                return {
                                    [_this.type + '_ko']: v,
                                    [_this.type + '_en']: arrEn[i]
                                }
                            });

                            try {
                                const opt = {
                                    url: '{{route('news.ajax.post.classification')}}',
                                    data: {
                                        type: _this.type,
                                        data: sendData
                                    }
                                };
                                const response = await commonJs.sendPromiseAjax(opt);
                                if (response?.resultCode && response.resultCode === '0000') {
                                    _this.createwrap.empty();
                                } else {
                                    alert(response.message);
                                }
                                if (response?.data && response.data.length > 0) {
                                    _this.setDataListHtml(response.data);
                                }
                            } catch (e) {
                                alert(e);
                            }
                        }
                    }
                });
            },
            listAction: function() {
                const _this = this;
                $('#classification-wrapper').on('click', '.btn-'+_this.type+'-list', function() {
                    const no = $(this).data('no');
                    const cateInfo = _this.classificationData.filter(v => v[_this.type+'_no'] === parseInt(no));
                    if (cateInfo[0]) {
                        const modal = $('#defaultModal');
                        modal.find('#defaultModalLabel').text(_this.title);
                        modal.find('input[name="no"]').val(no);
                        modal.find('input[name="type"]').val(_this.type);
                        modal.find('#modal_use').prop('checked', (cateInfo[0].use === 'y'));
                        modal.find('#updateAdmin').text(cateInfo[0].update_admin + ' | ' + cateInfo[0].updated_at);
                        modal.find('input[name="modal_ko"]').val(cateInfo[0][_this.type+'_ko']);
                        modal.find('input[name="modal_en"]').val(cateInfo[0][_this.type+'_en']);
                    }
                });
            },
            getList: async function() {
                const opt = {
                    url: this.listUrl,
                    method: 'get'
                }
                try {
                    const response = await commonJs.sendPromiseAjax(opt);
                    if (response?.resultCode === '0000' && response?.data) {
                        console.log(response)
                        this.setDataListHtml(response.data);
                    }
                } catch (e) {}
            },
            init: function() {
                const _this = this;
                _this.getList();
                _this.insertAction();
                _this.listAction();
            }
        }

        $(function() {
            const category = new Classification();
            category.title = '카테고리';
            category.listUrl = '{{route('news.ajax.get.category')}}';
            category.listWrap = $('#cate-list-wrap');
            category.createwrap = $('#cate-create-wrap');
            category.createBtn = $('#btn-cate-create');
            category.createSubmitBtn = $('#btn-cate-submit');
            category.type = 'cate';
            category.init();

            const hashtag = new Classification();
            hashtag.title = '해시태그';
            hashtag.listUrl = '{{route('news.ajax.get.tags')}}';
            hashtag.listWrap = $('#tag-list-wrap');
            hashtag.createwrap = $('#tag-create-wrap');
            hashtag.createBtn = $('#btn-tag-create');
            hashtag.createSubmitBtn = $('#btn-tag-submit');
            hashtag.type = 'tag';
            hashtag.init();

            const modalObj = new bootstrap.Modal(document.getElementById('defaultModal'), {
                keyboard: false
            });

            $('#defaultModalSubmit').on({
                click: async function() {
                    const modal = $('#defaultModal');
                    const no = modal.find('input[name="no"]').val();
                    const type = modal.find('input[name="type"]').val();
                    const use = modal.find('#modal_use').prop('checked') ? 'y' : 'n';
                    const ko = modal.find('input[name="modal_ko"]').val();
                    const en = modal.find('input[name="modal_en"]').val();
                    const sendData = {
                        type: type,
                        no: no,
                        use: use,
                        ko: ko,
                        en: en,
                    };
                    let classificationData = false;
                    if (type === 'cate') {
                        classificationData = category.getClassificationData();
                    } else if (type === 'tag') {
                        classificationData = hashtag.getClassificationData();
                    }

                    if (classificationData) {
                        const dataInfo = classificationData.filter(v => v[type+'_no'] === parseInt(no));
                        if (dataInfo[0].use === sendData.use && dataInfo[0][type+'_ko'] === sendData.ko && dataInfo[0][type+'_en'] === sendData.en) {
                            alert('수정 사항이 없습니다.');
                            modalObj.hide();
                            return false;
                        }
                        const opt = {
                            url: '{{route('news.ajax.put.classification')}}',
                            method: 'put',
                            data: {...sendData}
                        }
                        try {
                            const response = await commonJs.sendPromiseAjax(opt);
                            if (response?.resultCode === '0000') {
                                if (response?.data) {
                                    if (type === 'cate') {
                                        console.log('뭐야야야야')
                                        category.setDataListHtml(response.data);
                                    } else if (type === 'tag') {
                                        console.log('ha 181818181818')
                                        hashtag.setDataListHtml(response.data);
                                    }
                                }
                            } else {
                                alert(response.message);
                            }
                            modalObj.hide();
                        } catch (e) {
                            alert('네트워크 에러 잠시 후 다시 시도해 주세요.');
                            modalObj.hide();
                        }
                    } else {
                        modalObj.hide();
                    }
                }
            });
        });
    </script>
@endsection
