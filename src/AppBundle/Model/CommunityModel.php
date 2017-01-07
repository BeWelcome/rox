<?php
/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/22/16
 * Time: 12:36 AM
 */
namespace AppBundle\Model;

use AppBundle\Entity\CommunityNews;
use InvalidArgumentException;

class CommunityModel extends BaseModel
{
    /**
     * @return CommunityNews | array
     */
    public function getLatest($count = 1)
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Count must be at least 1');
        }

        $result = $this
            ->em
            ->getRepository(CommunityNews::class)
            ->createQuerybuilder('c')
            ->where('c.public = 1')
            ->setMaxResults($count)
            ->addOrderBy('c.createdAt', 'desc')
            ->getQuery()
            ->getResult();

        if ($count === 1) {
            return $result[0];
        }

        return $result;
    }
}