<?php
/**
 * ownCloud - Calendar App
 *
 * @author Georg Ehrke
 * @copyright 2014 Georg Ehrke <oc.list@georgehrke.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\Calendar\BusinessLayer;

use OCP\AppFramework\Http;

use OCP\Calendar\ISubscription;
use OCP\Calendar\ISubscriptionCollection;
use OCP\Calendar\DoesNotExistException;
use OCP\Calendar\MultipleObjectsReturnedException;

use OCA\Calendar\Db\SubscriptionMapper;

class SubscriptionBusinessLayer extends BusinessLayer {

	/**
	 * @var SubscriptionMapper
	 */
	protected $mapper;


	/**
	 * @param string $userId
	 * @param integer $limit
	 * @param integer $offset
	 * @return ISubscriptionCollection
	 */
	public function findAll($userId, $limit, $offset) {
		return $this->mapper->findAll($userId, $limit, $offset);
	}


	/**
	 * @param string $userId
	 * @return integer
	 */
	public function count($userId) {
		return $this->mapper->count($userId);
	}


	/**
	 * @param string $userId
	 * @param string $type
	 * @param integer $limit
	 * @param integer $offset
	 * @return ISubscriptionCollection
	 */
	public function findAllByType($userId, $type, $limit, $offset) {
		return $this->mapper->findAllByType($userId, $type, $limit, $offset);
	}


	/**
	 * @param string $userId
	 * @param string $type
	 * @return integer
	 */
	public function countByType($userId, $type) {
		return $this->mapper->countByType($userId, $type);
	}


	/**
	 * @param int $id
	 * @param string $userId
	 * @throws BusinessLayerException
	 * @return ISubscription
	 */
	public function find($id, $userId) {
		try {
			return $this->mapper->find($id, $userId);
		} catch(DoesNotExistException $ex) {
			throw new BusinessLayerException($ex->getMessage(), Http::STATUS_NOT_FOUND, $ex);
		} catch(MultipleObjectsReturnedException $ex) {
			throw new BusinessLayerException($ex->getMessage(), HTTP::STATUS_INTERNAL_SERVER_ERROR, $ex);
		}
	}


	/**
	 * @param int $id
	 * @param string $userId
	 * @return bool
	 */
	public function doesExist($id, $userId) {
		return $this->mapper->doesExist($id, $userId);
	}


	/**
	 * @param int $id
	 * @param string $type
	 * @param string $userId
	 * @return mixed
	 */
	public function doesExistOfType($id, $type, $userId) {
		return $this->mapper->doesExistOfType($id, $type, $userId);
	}


	/**
	 * @param ISubscription $subscription
	 * @throws BusinessLayerException
	 * @return ISubscription
	 */
	public function create(ISubscription $subscription) {
		if (!$subscription->isValid()) {
			throw new BusinessLayerException('Subscription is not valid', Http::STATUS_UNPROCESSABLE_ENTITY);
		}

		return $this->mapper->insert($subscription);
	}


	/**
	 * @param ISubscription $subscription
	 * @throws BusinessLayerException
	 * @return ISubscription
	 */
	public function update(ISubscription $subscription) {
		if (!$subscription->isValid()) {
			throw new BusinessLayerException('Subscription is not valid', Http::STATUS_UNPROCESSABLE_ENTITY);
		}

		$this->mapper->update($subscription);

		return $subscription;
	}


	/**
	 * @param ISubscription $subscription
	 */
	public function delete(ISubscription $subscription) {
		$this->mapper->delete($subscription);
	}
}