const redis = require('redis');
const logger = require('../utils/logger');

class RedisQueue {
  constructor() {
    this.client = null;
    this.isConnected = false;
  }

  async connect() {
    try {
      this.client = redis.createClient({
        socket: {
          host: process.env.REDIS_HOST || 'localhost',
          port: parseInt(process.env.REDIS_PORT) || 6379,
        },
        password: process.env.REDIS_PASSWORD || undefined,
        database: parseInt(process.env.REDIS_DB) || 0,
      });

      this.client.on('error', (err) => {
        logger.error('Redis Client Error:', err);
        this.isConnected = false;
      });

      this.client.on('connect', () => {
        logger.info('Redis connected successfully');
        this.isConnected = true;
      });

      await this.client.connect();
      return true;
    } catch (error) {
      logger.error('Failed to connect to Redis:', error);
      return false;
    }
  }

  async pushGpsData(data) {
    if (!this.isConnected) {
      logger.warn('Redis not connected, cannot push GPS data');
      return false;
    }

    try {
      const queueName = process.env.QUEUE_GPS_DATA || 'gps:data:incoming';
      await this.client.rPush(queueName, JSON.stringify(data));
      logger.debug(`GPS data pushed to queue: ${queueName}`);
      return true;
    } catch (error) {
      logger.error('Failed to push GPS data to queue:', error);
      return false;
    }
  }

  async pushDeviceStatus(data) {
    if (!this.isConnected) {
      logger.warn('Redis not connected, cannot push device status');
      return false;
    }

    try {
      const queueName = process.env.QUEUE_DEVICE_STATUS || 'device:status:updates';
      await this.client.rPush(queueName, JSON.stringify(data));
      logger.debug(`Device status pushed to queue: ${queueName}`);
      return true;
    } catch (error) {
      logger.error('Failed to push device status to queue:', error);
      return false;
    }
  }

  async publish(channel, message) {
    if (!this.isConnected) {
      logger.warn('Redis not connected, cannot publish message');
      return false;
    }

    try {
      await this.client.publish(channel, JSON.stringify(message));
      logger.debug(`Message published to channel: ${channel}`);
      return true;
    } catch (error) {
      logger.error('Failed to publish message:', error);
      return false;
    }
  }

  async disconnect() {
    if (this.client) {
      await this.client.quit();
      this.isConnected = false;
      logger.info('Redis disconnected');
    }
  }
}

module.exports = new RedisQueue();
