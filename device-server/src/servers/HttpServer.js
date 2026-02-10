const express = require('express');
const bodyParser = require('body-parser');
const logger = require('../utils/logger');
const redisQueue = require('../queue/RedisQueue');

/**
 * HTTP Server for GPS Device Push Data
 * Accepts JSON payloads from devices via HTTP POST
 */
class HttpServer {
  constructor() {
    this.app = express();
    this.server = null;
    
    this.setupMiddleware();
    this.setupRoutes();
  }

  setupMiddleware() {
    // Parse JSON bodies
    this.app.use(bodyParser.json());
    
    // Parse URL-encoded bodies
    this.app.use(bodyParser.urlencoded({ extended: true }));

    // Request logging
    this.app.use((req, res, next) => {
      logger.debug(`HTTP ${req.method} ${req.path} from ${req.ip}`);
      next();
    });
  }

  setupRoutes() {
    // Health check
    this.app.get('/health', (req, res) => {
      res.json({ status: 'ok', timestamp: new Date().toISOString() });
    });

    // Device data push endpoint
    this.app.post('/api/device/push', async (req, res) => {
      await this.handleDevicePush(req, res);
    });

    // Batch data push
    this.app.post('/api/device/push/batch', async (req, res) => {
      await this.handleBatchPush(req, res);
    });

    // Stats endpoint
    this.app.get('/api/stats', (req, res) => {
      res.json(this.getStats());
    });

    // 404 handler
    this.app.use((req, res) => {
      res.status(404).json({ error: 'Endpoint not found' });
    });

    // Error handler
    this.app.use((err, req, res, next) => {
      logger.error('HTTP Server error:', err);
      res.status(500).json({ error: 'Internal server error' });
    });
  }

  /**
   * Handle single device data push
   */
  async handleDevicePush(req, res) {
    try {
      const data = req.body;

      // Validate required fields
      if (!data.imei) {
        return res.status(400).json({ error: 'IMEI is required' });
      }

      if (data.latitude === undefined || data.longitude === undefined) {
        return res.status(400).json({ error: 'Latitude and longitude are required' });
      }

      // Validate coordinates
      if (data.latitude < -90 || data.latitude > 90) {
        return res.status(400).json({ error: 'Invalid latitude' });
      }

      if (data.longitude < -180 || data.longitude > 180) {
        return res.status(400).json({ error: 'Invalid longitude' });
      }

      // Add metadata
      const gpsData = {
        ...data,
        protocol: 'HTTP',
        serverTime: new Date().toISOString(),
        sourceIp: req.ip,
      };

      // Push to Redis queue
      const success = await redisQueue.pushGpsData(gpsData);

      if (success) {
        logger.info(`HTTP: Received GPS data from IMEI ${data.imei}`);
        res.json({ success: true, message: 'Data received' });
      } else {
        res.status(500).json({ error: 'Failed to queue data' });
      }
    } catch (error) {
      logger.error('HTTP: Error handling device push:', error);
      res.status(500).json({ error: 'Internal server error' });
    }
  }

  /**
   * Handle batch data push
   */
  async handleBatchPush(req, res) {
    try {
      const { data } = req.body;

      if (!Array.isArray(data)) {
        return res.status(400).json({ error: 'Data must be an array' });
      }

      if (data.length === 0) {
        return res.status(400).json({ error: 'Data array is empty' });
      }

      if (data.length > 100) {
        return res.status(400).json({ error: 'Maximum 100 records per batch' });
      }

      let successCount = 0;
      let failCount = 0;

      for (const item of data) {
        // Basic validation
        if (!item.imei || item.latitude === undefined || item.longitude === undefined) {
          failCount++;
          continue;
        }

        const gpsData = {
          ...item,
          protocol: 'HTTP_BATCH',
          serverTime: new Date().toISOString(),
          sourceIp: req.ip,
        };

        const success = await redisQueue.pushGpsData(gpsData);
        if (success) {
          successCount++;
        } else {
          failCount++;
        }
      }

      logger.info(`HTTP Batch: Processed ${successCount} records, ${failCount} failed`);

      res.json({
        success: true,
        total: data.length,
        successful: successCount,
        failed: failCount,
      });
    } catch (error) {
      logger.error('HTTP: Error handling batch push:', error);
      res.status(500).json({ error: 'Internal server error' });
    }
  }

  /**
   * Start HTTP server
   */
  start(port = 3000) {
    this.server = this.app.listen(port, () => {
      logger.info(`HTTP Server listening on port ${port}`);
    });
  }

  /**
   * Get server statistics
   */
  getStats() {
    return {
      uptime: process.uptime(),
      memory: process.memoryUsage(),
      timestamp: new Date().toISOString(),
    };
  }

  /**
   * Stop HTTP server
   */
  async stop() {
    if (this.server) {
      await new Promise((resolve) => {
        this.server.close(() => {
          logger.info('HTTP Server stopped');
          resolve();
        });
      });
    }
  }
}

module.exports = HttpServer;
