<div>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
        @if($pagination['prev'] > 0)
            <li class="page-item">
                <a class="btn-page-number page-link" href="#" data-page-number="1" aria-label="Previous">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a class="btn-page-number page-link" href="#" data-page-number="{{$pagination['prev']}}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        @endif
        @for($i = $pagination['firstNumber']; $i <= $pagination['lastNumber']; $i++)
            <li class="page-item @if($i == $pagination['page']) active @endif">
                @if($i == $pagination['page'])
                    <span class="page-link">{{$i}}</span>
                @else
                    <a class="btn-page-number page-link" data-page-number="{{$i}}" href="#">{{$i}}</a>
                @endif
            </li>
        @endfor
        @if($pagination['next'] <= $pagination['totalPage'])
            <li class="page-item">
                <a class="btn-page-number page-link" href="#" data-page-number="{{$pagination['next']}}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a class="btn-page-number page-link" href="#" data-page-number="{{$pagination['totalPage']}}" aria-label="Next">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
        @endif
        </ul>
    </nav>
</div>
