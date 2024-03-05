<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsService
{
    public function __construct()
    {
    }

    public function getCategoriesByNewsNum()
    {

    }

    public function getTagsByNewsNum()
    {

    }

    /**
     * @param array $where table :: news_categories as nc, categories as c, news as n
     * @param array $orderBy [ [column: string, direction:'desc|asc'], ... ]
     * @param int $offset
     * @param int $limit
     * @return bool|array
     */
    public function getNewsByCategories(array $where = [], array $orderBy = [], int $offset = 0, int $limit= 21): bool|array
    {
        try {
            $builder = DB::table('news_categories as nc')
                ->leftJoin('categories as c', 'c.cate_no', '=', 'nc.cate_no')
                ->join('news as n', 'n.news_no', '=', 'nc.news_no');

            if (count($where) > 0) {
                $builder->where($where);
            }

            $total = $builder->count();

            foreach($orderBy as $row) {
                $builder->orderBy($row[0], $row[1]);
            }

            $data = $builder->orderByDesc('n.service_date')
                ->orderByDesc('n.created_at')
                ->select('*', 'c.use as cate_use', 'n.use as news_use', 'c.hit as cate_hit', 'n.hit as news_hit', 'n.created_at as news_created_at', 'n.update_admin as news_update_admin')
                ->selectRaw('date_format(n.service_date, "%Y-%m-%d") as service_date')
                ->offset($offset)
                ->limit($limit)
                ->get();

            return [
                'total' => $total,
                'list' => $data,
            ];

        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'getNewsCategories', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param int $tag_no
     * @param array $where
     * @param array $orderBy
     * @param int $offset
     * @param int $limit
     * @return array|false
     */
    public function getNewsByTags(int $tag_no, array $where = [], array $orderBy = [], int $offset = 0, int $limit= 21): bool|array
    {
        try {
            $builder = DB::table('news_tags as nc')
                ->join('news as n', 'n.news_no', '=', 'nc.news_no')
                ->join('tags as t', 't.tag_no', '=', 'nc.tag_no')
                ->join('news_categories as nt', 'nt.news_no', '=', 'n.news_no')
                ->join('categories as c', 'c.cate_no', '=', 'nt.cate_no')
                ->where('nc.tag_no', '=', $tag_no);

            if (count($where) > 0) {
                $builder->where($where);
            }

            $total = $builder->count();

            foreach($orderBy as $row) {
                $builder->orderBy($row[0], $row[1]);
            }

            $data = $builder->orderByDesc('n.service_date')
                ->orderByDesc('n.created_at')
                ->select('*', 'c.use as cate_use', 'n.use as news_use', 'c.hit as cate_hit', 't.hit as tag_hit', 'n.hit as news_hit', 'n.created_at as news_created_at', 'n.update_admin as news_update_admin')
                ->selectRaw('date_format(n.service_date, "%Y-%m-%d") as service_date')
                ->offset($offset)
                ->limit($limit)
                ->get();

            return [
                'total' => $total,
                'list' => $data,
            ];

        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'getNewsByTags', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param int $news_no
     * @return array|false
     */
    public function getNewsDetail(int $news_no): bool|array
    {
        try {
            $detail = DB::table('news_categories as nc')
                ->select('nc.no', 'c.cate_no', 'c.cate_ko', 'c.cate_en', 'c.use as cate_use',
                    'n.news_no', 'n.subject_ko', 'n.subject_en', 'n.contents_ko', 'n.contents_en', 'n.update_admin', 'n.use as news_use',
                    'n.service', 'n.hit as news_hit')
                ->selectRaw('date_format(n.service_date, "%Y-%m-%d") as service_date, date_format(n.created_at, "%Y-%m-%d %H:%i:%s") as news_created_at,  date_format(n.updated_at, "%Y-%m-%d %H:%i:%s") as news_updated_at')
                ->leftJoin('categories as c', 'c.cate_no', '=', 'nc.cate_no')
                ->join('news as n', 'n.news_no', '=', 'nc.news_no')
                ->where('nc.news_no', '=', $news_no)
                ->first();

            $tags = DB::table('news_tags as nt')
                ->select('nt.no as nt_no', 't.tag_no', 't.tag_ko', 't.tag_en', 't.use as tag_use')
                ->join('tags as t', 't.tag_no', '=', 'nt.tag_no')
                ->where('nt.news_no', '=', $news_no)
                ->get();

            return [
                'detail' => $detail,
                'tags' => $tags
            ];
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'getNewsDetail', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function updateNewsDetail(array $params): bool
    {
        try {
            DB::beginTransaction();

            DB::table('news_categories')
                ->where('no', '=', $params['no'])
                ->update(['cate_no' => $params['cate_no'], 'updated_at' => date('Y-m-d H:i:s', time())]);

            DB::table('news')
                ->where('news_no', '=', $params['news_no'])
                ->update([
                    'subject_ko' => $params['subject_ko'],
                    'subject_en' => $params['subject_en'],
                    'contents_ko' => $params['contents_ko'],
                    'contents_en' => $params['contents_en'],
                    'use' => $params['use'],
                    'service_date' => $params['service_date'],
                    'update_admin' => $params['update_admin'],
                    'updated_at' => date('Y-m-d H:i:s', time()),
                ]);

            if (!empty($params['insert_tag']) && count($params['insert_tag']) > 0) {
                $insertTags = [];
                foreach ($params['insert_tag'] as $val) {
                    $insertTags[] = [
                        'news_no' => $params['news_no'],
                        'tag_no' => $val,
                        'created_at' => date('Y-m-d H:i:s', time()),
                        'updated_at' => date('Y-m-d H:i:s', time()),
                    ];
                }
                DB::table('news_tags')->insert($insertTags);
            }

            if (!empty($params['delete_tag']) && count($params['delete_tag']) > 0) {
                DB::table('news_tags')
                    ->where('news_no', '=', $params['news_no'])
                    ->whereIn('tag_no', $params['delete_tag'])
                    ->delete();
            }

            DB::commit();

            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateNewsDetail', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param $news_Ids
     * @param $use
     * @return false|int
     */
    public function updateNewsAllUse($news_Ids, $use): bool|int
    {
        try {
            return DB::table('news')->whereIn('news_no', $news_Ids)->update(['use' => $use]);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateNewsAllUse', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $news_Ids
     * @param array $updateData
     * @return false|int
     */
    public function updateNewsMultipleRow(array $news_Ids, array $updateData): bool|int
    {
        try {
            return DB::table('news')->whereIn('news_no', $news_Ids)->update($updateData);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateNewsMultipleRow', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $newsData
     * @param array $newsUniqueIds
     * @param array $newsCateData
     * @param array $newsTagData
     * @return bool
     */
    public function insertNews(array $newsData, array $newsUniqueIds, array $newsCateData, array $newsTagData): bool
    {
        try {
            DB::beginTransaction();

            DB::table('news')->insert($newsData);

            $insertedIds = DB::table('news')->select('news_no')->whereIn('multiple_insert_check_id', $newsUniqueIds)->get();

            foreach ($newsCateData as $len => $row) {
                $newsCateData[$len]['news_no'] = $insertedIds[$len]->news_no;
            }

            DB::table('news_categories')->insert($newsCateData);

            $newNewsTagData = [];
            foreach ($insertedIds as $len => $val) {
                foreach ($newsTagData[$len] as $len2 => $val2) {
                    $newsTagData[$len][$len2]['news_no'] = $val->news_no;
                    $newNewsTagData[] = $newsTagData[$len][$len2];
                }
            }

            if (count($newNewsTagData) > 0) {
                DB::table('news_tags')->insert($newNewsTagData);
            }

            DB::commit();

            return true;

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'insertNews', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $arrKo
     * @param array $arrEn
     * @return false|Collection
     */
    public function getUsedCategory(array $arrKo, array $arrEn): bool|Collection
    {
        try {
            return DB::table('categories')->select('cate_ko', 'cate_en')
                ->whereIn('cate_ko', array_merge($arrKo, $arrEn))
                ->orWhereIn('cate_en', array_merge($arrEn, $arrKo))
                ->get();
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'getUsedCategory', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $arrKo
     * @param array $arrEn
     * @return false|Collection
     */
    public function getUsedTags(array $arrKo, array $arrEn): bool|Collection
    {
        try {
            return DB::table('tags')->select('tag_ko', 'tag_en')
                ->whereIn('tag_ko', array_merge($arrKo, $arrEn))
                ->orWhereIn('tag_en', array_merge($arrEn, $arrKo))
                ->get();
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'getUsedTags', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function insertTags(array $data): bool
    {
        try {
            return DB::table('tags')->insert($data);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'insertTags', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function insertCategory(array $data): bool
    {
        try {
            return DB::table('categories')->insert($data);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'insertCategory', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param int $no
     * @param array $data
     * @return false|int
     */
    public function updateCategory(int $no, array $data): bool|int
    {
        try {
            return DB::table('categories')->where('cate_no', $no)->update($data);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateCategory', 'message' => $e]));
            return false;
        }
    }

    /**
     * @param int $no
     * @param array $data
     * @return false|int
     */
    public function updateTags(int $no, array $data): bool|int
    {
        try {
            return DB::table('tags')->where('tag_no', $no)->update($data);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateTags', 'message' => $e]));
            return false;
        }
    }
}
