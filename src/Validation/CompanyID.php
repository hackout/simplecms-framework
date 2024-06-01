<?php
namespace SimpleCMS\Framework\Validation;

/**
 * 统一社会信用代码
 *
 * @author Dennis Lui <hackout@vip.qq.com>
 */
class CompanyID
{
    protected string $companyId;

    protected $regex = '^([0-9A-HJ-NPQRTUWXY]{2}\d{6}[0-9A-HJ-NPQRTUWXY]{10}|[1-9]\d{14})$';

    /**
     * IDCard constructor.
     * @param string $companyId
     */
    public function __construct(string $companyId)
    {
        $this->setCompanyId($companyId);
    }

    /**
     * Get CompanyId
     */
    public function getCompanyId()
    {
        return (string) $this->companyId;
    }

    /**
     * @param mixed $companyId
     */
    public function setCompanyId(string $companyId)
    {
        $this->companyId = (string) trim(strtoupper($companyId));
    }

    /**
     * 统一社会信用代码是否有效
     * @return bool
     */
    public function isValid(): bool
    {
        if (strlen($this->companyId) != 18) {
            return false;
        }
        return preg_match($this->regex, $this->companyId);
    }

}