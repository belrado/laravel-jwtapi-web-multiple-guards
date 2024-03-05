<div class="Container">
    <ul class="nav nav-tabs" id="{{$tabId}}">
        <li class="nav-item">
            <a class="nav-link active text-dark" data-cate-no="all" aria-current="page" href="#">All</a>
        </li>
        @foreach($tabType as $row)
            <li class="nav-item">
                <a class="nav-link @if($row->use === 'n') text-black-50 @else text-dark @endif" href="#" data-cate-no="{{$row->cate_no}}">{{$row->cate_ko}}@if($row->use === 'n') <span style="font-size:12px">[사용중지]</span>@endif</a>
            </li>
        @endforeach
    </ul>
</div>
