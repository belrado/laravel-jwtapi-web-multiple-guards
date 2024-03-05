<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants\Response;
use App\Facade\CommonFacade;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use App\Services\NewsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    protected NewsService $newsService;

    public function __construct()
    {
        $this->newsService = new NewsService();
    }

    public function getNews(Request $request)
    {
        $params = $request->input();

        $validate = [
            'type' => 'required|string|in:newsCategories,newsTags',
            'mode' => 'required|string|in:web,app',
            'page' => 'required|int'
        ];
        if (!empty($params['type']) && $params['type'] === 'newsTags') {
            $validate['tag_no'] = 'required|int';
        }

        $validator = Validator::make($params, $validate);
        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '필수 항목 누락 또는 잘못된 데이터가 었습니다.']);
        }

        $block = 5;
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 20;
        $offset = ($page - 1) * $limit;

        try {
            // 관리자인지 확인하기 위해 (관리자모드는 웹에서만 가능)
            $userType = Auth::user()->type;
        } catch (Exception $e) {
            $userType = null;
        }

        if ($params['type'] === 'newsCategories') {
            if ($userType === 'admin') {
                // 관리자라면 모든 데이터를 불러온다
                $where = [];
            } else {
                // 관리자가 아니라면 사용하는 데이터만 불러온다
                $where = [
                    ['n.use', '=', 'y'],
                    ['c.use', '=', 'y']
                ];
            }
            if (!empty($params['cate_no']) && is_numeric($params['cate_no'])) {
                $where[] = ['nc.cate_no', '=', $params['cate_no']];
            }
            $result = $this->newsService->getNewsByCategories($where, [], $offset, ($limit + 1));
        } else {
            // app 에서만 사용
            $where = [
                ['n.use', '=', 'y'],
                ['c.use', '=', 'y'],
                ['t.use', '=', 'y']
            ];
            $result = $this->newsService->getNewsByTags($params['tag_no'], $where, [], $offset, ($limit + 1));
        }

        if (! $result) {
            return response()->json(['resultCode' => Response::ERROR, 'message' => '데이터를 불러오지 못했습니다. 잠시 후 다시 시도해주세요.']);
        }

        $data =[];
        foreach ($result['list'] as $len => $row) {
            if ($len >= $limit) continue;
           // $data[$len] = $row;
            foreach ($row as $key => $val) {
                if ($key === 'subject_ko' || $key === 'subject_en') {
                    $data[$len][$key] = htmlspecialchars(stripslashes($val));
                } else {
                    $data[$len][$key] = $val;
                }
            }
        }

        if ($params['mode'] === 'web') {
            // 페이징
            $paginationHtml= CommonFacade::getPaginationHtml($result['total'], $page, $limit, $block);
            return response()->json(['resultCode' => Response::SUCCESS, 'data' => $data, 'total' => $result['total'], 'pageTotal' => ($result['total'] - $offset), 'paginationHtml' => $paginationHtml]);
        } else {
            // 더보기
            $nextCheck = count($result['list']) > $limit;
            $page = $nextCheck ? $page +1 : $page;
            return response()->json(['resultCode' => Response::SUCCESS, 'data' => $data, 'total' => $result['total'], 'next' => $nextCheck, 'page' => $page]);
        }
    }

    public function getNewsDetail(Request $request)
    {
        $params = $request->input();

        $validator = Validator::make($params, [
            'news_no' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '필수 항목 누락 또는 잘못된 데이터가 었습니다.']);
        }

        if ($result = $this->newsService->getNewsDetail($params['news_no'])) {
            return response()->json(['resultCode' => Response::SUCCESS, 'data' => $result]);
        } else {
            return response()->json(['resultCode' => Response::ERROR, 'message' => '데이터를 불러오지 못했습니다. 잠시 후 다시 시도해주세요.']);
        }
    }

    public function insertNews(Request $request)
    {
        $params = $request->input();
        $newsUniqueIds = [];
        $newsData = [];
        $newsCateData = [];
        $newsTagData = [];

        foreach ($params as $len => $row) {
            $validator = Validator::make($row, [
                'cate_no' => 'required|int',
                'subject_ko' => 'required|string',
                'subject_en' => 'required|string',
                'contents_ko' => 'required',
                'contents_en' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['resultCode' => Response::FAIL, 'message' => ($len+1).'번째 게시물 필수 항목 누락 또는 잘못된 데이터가 었습니다.']);
            }

            $newsUniqueId = microtime(true).'_'.$row['cate_no'].'_'.CommonFacade::GenerateString(10, true, false);
            $newsData[$len] = [
                'multiple_insert_check_id' => $newsUniqueId,
                'subject_ko' => $row['subject_ko'],
                'subject_en' => $row['subject_en'],
                'contents_ko' => $row['contents_ko'],
                'contents_en' => $row['contents_en'],
                'update_admin' => Auth::user()->email,
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time()),
            ];

            $newsUniqueIds[$len] = $newsUniqueId;

            $newsCateData[$len] = [
                'news_no' => null,
                'cate_no' => $row['cate_no'],
            ];

            if (!empty($row['tags']) && count($row['tags']) > 0) {
                foreach ($row['tags'] as $tVal) {
                    $newsTagData[$len][] = [
                        'news_no' => null,
                        'tag_no' => $tVal,
                    ];
                }
            } else {
                $newsTagData[$len] = [];
            }
        }

        //return response()->json(['resultCode' => Response::FAIL, 'message' => '에러야', '$newsTagData' => $newsTagData]);

        if ($this->newsService->insertNews($newsData, $newsUniqueIds, $newsCateData, $newsTagData)) {
            return response()->json(['resultCode' => Response::SUCCESS]);
        } else {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '에러야', '$params' => $params]);
        }

       // return response()->json(['resultCode' => Response::FAIL, 'message' => '필수 항목이 누락되었습니다.', '$params' => $params]);
    }

    public function updateNewsDetail(Request $request)
    {
        $params = $request->input();

        $validator = Validator::make($params, [
            'no' => 'required|int',
            'news_no' => 'required|int',
            'cate_no' => 'required|int',
            'use' => 'required|string|in:y,n',
            'subject_ko' => 'required|string',
            'subject_en' => 'required|string',
            'contents_ko' => 'required|string',
            'contents_en' => 'required|string',
            'insert_tag' => 'array',
            'delete_tag' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '게시물 필수 항목 누락 또는 잘못된 데이터가 었습니다.']);
        }

        $params['update_admin'] = Auth::user()->email;
        if ($this->newsService->updateNewsDetail($params)) {
            return response()->json(['resultCode' => Response::SUCCESS]);
        } else {
            return response()->json(['resultCode' => Response::ERROR, 'message' => '데이터를 수정하지 못했습니다. 잠시 후 다시 시도해주세요.']);
        }
    }

    public function updateNewsAllUse(Request $request)
    {
        $params = $request->input();

        $validator = Validator::make($params, [
            'news_ids' => 'required|array',
            'use' => 'required|string|in:y,n',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '게시물 필수 항목 누락 또는 잘못된 데이터가 었습니다.']);
        }

        $updateData = [
            'use' => $params['use'],
            'update_admin' => Auth::user()->email,
            'updated_at' => date('Y-m-d H:i:s', time())
        ];
        if ($this->newsService->updateNewsMultipleRow($params['news_ids'], $updateData)) {
            return response()->json(['resultCode' => Response::SUCCESS]);
        } else {
            return response()->json(['resultCode' => Response::ERROR, 'message' => '데이터를 수정하지 못했습니다. 잠시 후 다시 시도해주세요.']);
        }
    }

    public function updateNewsAllServiceDate(Request $request)
    {
        $params = $request->input();

        $validator = Validator::make($params, [
            'news_ids' => 'required|array',
            'service_date' => 'required|string|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '게시물 필수 항목 누락 또는 잘못된 데이터가 었습니다.']);
        }

        $updateData = [
            'service_date' => $params['service_date'],
            'update_admin' => Auth::user()->email,
            'updated_at' => date('Y-m-d H:i:s', time())
        ];
        if ($this->newsService->updateNewsMultipleRow($params['news_ids'], $updateData)) {
            return response()->json(['resultCode' => Response::SUCCESS]);
        } else {
            return response()->json(['resultCode' => Response::ERROR, 'message' => '데이터를 수정하지 못했습니다. 잠시 후 다시 시도해주세요.']);
        }
    }

    public function insertClassification(Request $request)
    {
        $params = $request->input();

        $validator = Validator::make($params, [
            'type' => 'required|string|in:cate,tag',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '필수 항목이 누락되었습니다.']);
        }

        $columnName = $params['type'];

        $arrKo = [];
        $arrEn = [];
        $data = [];
        foreach ($params['data'] as $len => $row) {
            if (!empty($row[$columnName.'_ko'])) $arrKo[$len] = $row[$columnName.'_ko'];
            if (!empty($row[$columnName.'_en'])) $arrEn[$len] = $row[$columnName.'_en'];
            $data[$len] = $row;
            $data[$len]['update_admin'] = Auth::user()->email;
            $data[$len]['created_at'] = date('Y-m-d H:i:s', time());
            $data[$len]['updated_at'] = date('Y-m-d H:i:s', time());
        }

        if (count($arrKo) === 0 || count($arrEn) === 0) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => "등록할 데이터가 없습니다."]);
        }

        if ($columnName === 'cate') {
            $resulUsed = $this->newsService->getUsedCategory($arrKo, $arrEn);
        } else {
            $resulUsed = $this->newsService->getUsedTags($arrKo, $arrEn);
        }

        if ($resulUsed) {
            $useData = [];
            $keyKo = $columnName.'_ko';
            $keyEn = $columnName.'_en';

            foreach($resulUsed as $val) {
                if (in_array(strtolower($val->$keyKo), array_map('strtolower', $arrKo))) {
                    $useData[] = $val->$keyKo;
                }
                if (in_array(strtolower($val->$keyEn), array_map('strtolower', $arrKo))) {
                    $useData[] = $val->$keyEn;
                }
                if (in_array(strtolower($val->$keyEn), array_map('strtolower', $arrEn))) {
                    $useData[] = $val->$keyEn;
                }
                if (in_array(strtolower($val->$keyKo), array_map('strtolower', $arrEn))) {
                    $useData[] = $val->$keyKo;
                }
            }

            if (count($useData) > 0) {
                $name = $params['type'] === 'cate' ? '카테고리' : '해시태그';
                return response()->json(['resultCode' => Response::FAIL, 'message' => implode("," , $useData) . " 이미 등록된 ".$name."값 입니다."]);
            }

            if ($columnName === 'cate') {
                $result = $this->newsService->insertCategory($data);
                $data = Category::get() ?? [];
            } else {
                $result = $this->newsService->insertTags($data);
                $data = Tag::get() ?? [];
            }

            if ($result) {
                return response()->json(['resultCode' => Response::SUCCESS, 'data' => $data]);
            }
        }

        Log::error(json_encode(['type' => 'PHP', 'error' => 'insertClassification', 'message' => 'newsService error']));
        return response()->json(['resultCode' => Response::ERROR, 'message' => '저장 실패 잠시 후 다시 시도해 주세요.']);
    }

    public function updateClassification(Request $request)
    {
        $params = $request->input();
        $validator = Validator::make($params, [
            'no' => 'required',
            'type' => 'required|string|in:cate,tag',
            'ko' => 'required|string',
            'en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Response::FAIL, 'message' => '필수 항목이 누락되었습니다.']);
        }

        $columnName = $params['type'];

        $updateData = [
            $columnName.'_ko' => $params['ko'],
            $columnName.'_en' => $params['en'],
            'use' => $params['use'],
            'update_admin' => Auth::user()->email,
            'updated_at' => date('Y-m-d H:i:s', time()),
        ];

        if ($columnName === 'cate') {
            $result = $this->newsService->updateCategory((int) $params['no'], $updateData);
            $data = Category::get() ?? [];
        } else {
            $result = $this->newsService->updateTags((int) $params['no'], $updateData);
            $data = Tag::get() ?? [];
        }

        if ($result) {
            return response()->json(['resultCode' => Response::SUCCESS, 'data' => $data]);
        } else {
            Log::error(json_encode(['type' => 'PHP', 'error' => 'updateClassification', 'message' => 'newsService error']));
            return response()->json(['resultCode' => Response::ERROR, 'message' => '저장 실패 잠시 후 다시 시도해주세요.']);
        }
    }

    public function getCategory(Request $request)
    {
        $params = $request->input();

        if (!empty($params['use']) && $params['use'] === 'y') {
            $category = Category::where('use', 'y')->get() ?? [];
        } else {
            $category = Category::get() ?? [];
        }

        return response()->json(['resultCode' => Response::SUCCESS, 'data' => $category]);
    }

    public function getTags(Request $request)
    {
        $params = $request->input();

        if (!empty($params['use']) && $params['use'] === 'y') {
            $tags = Tag::where('use', 'y')->get() ?? [];
        } else {
            $tags = Tag::get() ?? [];
        }

        return response()->json(['resultCode' => Response::SUCCESS, 'data' => $tags]);
    }
}
