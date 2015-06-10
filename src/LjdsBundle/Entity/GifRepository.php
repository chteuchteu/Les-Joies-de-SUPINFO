<?php

namespace LjdsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LjdsBundle\Helper\FacebookHelper;
use PDO;

/**
 * GifRepository
 */
class GifRepository extends EntityRepository
{
    public function findByGifState($gifState, $page=-1, $gifsPerPage=5)
    {
        $firstResult = $gifsPerPage * $page - $gifsPerPage;

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . $gifState)
            ->orderBy('g.publishDate', 'DESC');

		if ($page != -1) {
			$qb->setFirstResult($firstResult)
				->setMaxResults($gifsPerPage);
		}

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

	public function getTop($amount)
	{
		$gifs = $this->findByGifState(GifState::PUBLISHED, 1, $amount);

		$likes = FacebookHelper::getFacebookLikes($gifs);

		$list = [];
		foreach ($likes as $like)
			$list[] = $like['gif'];

		return $list;
	}

    public function findBySubmitter($submitter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->andWhere('g.submittedBy = :submittedBy')
            ->setParameter('submittedBy', $submitter)
            ->orderBy('g.publishDate', 'DESC');

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

    public function getPaginationPagesCount($gifState, $gifsPerPage)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(g.id)')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . $gifState);
        $query = $qb->getQuery();

        $gifsCount = intval($query->getSingleScalarResult());

        return ceil($gifsCount/$gifsPerPage);
    }

    public function getTopSubmitters()
    {
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare('SELECT submittedBy as name, COUNT(*) as gifsCount
                              FROM gif GROUP BY submittedBy ORDER BY gifsCount DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForFeed()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC');

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }
}
