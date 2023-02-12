<?php


namespace App\Rpc\Service;
use App\Model\Entity\CountryCode;
use App\Rpc\Lib\CountryCodeInterface;
use Swoft\Db\DB;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class CountryCodeService
 * @package App\Rpc\Service
 * @Service()
 */
class CountryCodeService implements CountryCodeInterface{


    /**
     * 根据$where条件获取对应的国家信息
     * @param array $where
     * @return mixed
     */
    public function get_country_code(array $where): array
    {
        return DB::table('country_code')->where($where)->first();
    }

    /**
     * 根据where条件判断国家信息是否存在
     * @param array $where
     * @return bool
     */public function is_exists(array $where): bool
    {
        return CountryCode::where($where)->exists();
    }
}
