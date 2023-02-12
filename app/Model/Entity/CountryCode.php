<?php declare(strict_types=1);


namespace App\Model\Entity;

use Swoft\Db\Annotation\Mapping\Column;
use Swoft\Db\Annotation\Mapping\Entity;
use Swoft\Db\Annotation\Mapping\Id;
use Swoft\Db\Eloquent\Model;


/**
 *
 * Class CountryCode
 *
 * @since 2.0
 *
 * @Entity(table="country_code", pool="db.pool")
 */
class CountryCode extends Model
{
    /**
     * 国家码信息表
     * @Id()
     * @Column()
     *
     * @var int
     */
    private $id;

    /**
     * 国家id
     *
     * @Column(name="country_id", prop="countryId")
     *
     * @var int
     */
    private $countryId;

    /**
     * 国家码
     *
     * @Column(name="area_code", prop="areaCode")
     *
     * @var string
     */
    private $areaCode;

    /**
     * 类型名称,英文
     *
     * @Column(name="name_cn", prop="nameCn")
     *
     * @var string
     */
    private $nameCn;

    /**
     * 类型名称,英文
     *
     * @Column(name="name_en", prop="nameEn")
     *
     * @var string
     */
    private $nameEn;


    /**
     * @param int $id
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param int $countryId
     *
     * @return void
     */
    public function setCountryId(int $countryId): void
    {
        $this->countryId = $countryId;
    }

    /**
     * @param string $areaCode
     *
     * @return void
     */
    public function setAreaCode(string $areaCode): void
    {
        $this->areaCode = $areaCode;
    }

    /**
     * @param string $nameCn
     *
     * @return void
     */
    public function setNameCn(string $nameCn): void
    {
        $this->nameCn = $nameCn;
    }

    /**
     * @param string $nameEn
     *
     * @return void
     */
    public function setNameEn(string $nameEn): void
    {
        $this->nameEn = $nameEn;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCountryId(): ?int
    {
        return $this->countryId;
    }

    /**
     * @return string
     */
    public function getAreaCode(): ?string
    {
        return $this->areaCode;
    }

    /**
     * @return string
     */
    public function getNameCn(): ?string
    {
        return $this->nameCn;
    }

    /**
     * @return string
     */
    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

}
