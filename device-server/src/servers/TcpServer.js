const net = require('net');
const logger = require('../utils/logger');
const redisQueue = require('../queue/RedisQueue');

// Import parsers
const HT02Parser = require('../parsers/HT02Parser');
const GT06Parser = require('../parsers/GT06Parser');
const ConcoxParser = require('../parsers/ConcoxParser');
const GenericParser = require('../parsers/GenericParser');

/**
 * TCP Server for GPS Device Connections
 * Handles multiple ports and protocols
 */
class TcpServer {
  constructor() {
    this.servers = new Map();
    this.connections = new Map();
    this.parsers = new Map();
    
    // Initialize parsers
    this.initializeParsers();
  }

  initializeParsers() {
    this.parsers.set('HT02', new HT02Parser());
    this.parsers.set('GT06', new GT06Parser());
    this.parsers.set('Concox', new ConcoxParser());
    this.parsers.set('Generic', new GenericParser());
  }

  /**
   * Start TCP server on specified port
   * @param {number} port - Port number
   * @param {string} protocol - Protocol name (HT02, GT06, etc.)
   */
  start(port, protocol = 'Generic') {
    const server = net.createServer((socket) => {
      this.handleConnection(socket, protocol);
    });

    server.on('error', (err) => {
      logger.error(`TCP Server error on port ${port}:`, err);
    });

    server.listen(port, () => {
      logger.info(`TCP Server listening on port ${port} for ${protocol} protocol`);
    });

    this.servers.set(port, { server, protocol });
  }

  /**
   * Handle new device connection
   * @param {net.Socket} socket - TCP socket
   * @param {string} protocol - Protocol name
   */
  handleConnection(socket, protocol) {
    const connectionId = `${socket.remoteAddress}:${socket.remotePort}`;
    
    logger.info(`New connection: ${connectionId} (${protocol})`);

    // Store connection info
    const connectionInfo = {
      socket,
      protocol,
      imei: null,
      connectedAt: new Date(),
      lastActivity: new Date(),
      dataBuffer: Buffer.alloc(0),
    };

    this.connections.set(connectionId, connectionInfo);

    // Set socket timeout
    const timeout = parseInt(process.env.CONNECTION_TIMEOUT) || 300000; // 5 minutes
    socket.setTimeout(timeout);

    // Handle data
    socket.on('data', (data) => {
      this.handleData(connectionId, data);
    });

    // Handle connection close
    socket.on('close', () => {
      logger.info(`Connection closed: ${connectionId}`);
      this.handleDisconnect(connectionId);
    });

    // Handle errors
    socket.on('error', (err) => {
      logger.error(`Socket error for ${connectionId}:`, err);
      this.handleDisconnect(connectionId);
    });

    // Handle timeout
    socket.on('timeout', () => {
      logger.warn(`Connection timeout: ${connectionId}`);
      socket.end();
      this.handleDisconnect(connectionId);
    });
  }

  /**
   * Handle incoming data from device
   * @param {string} connectionId - Connection identifier
   * @param {Buffer} data - Received data
   */
  async handleData(connectionId, data) {
    const conn = this.connections.get(connectionId);
    if (!conn) return;

    conn.lastActivity = new Date();

    // Append to buffer
    conn.dataBuffer = Buffer.concat([conn.dataBuffer, data]);

    logger.debug(`Received ${data.length} bytes from ${connectionId}`);

    // Try to parse the data
    const parser = this.parsers.get(conn.protocol);
    if (!parser) {
      logger.error(`No parser found for protocol: ${conn.protocol}`);
      return;
    }

    try {
      const parsedData = parser.parse(conn.dataBuffer);

      if (parsedData) {
        // Valid data parsed
        logger.info(`Parsed data from ${connectionId}:`, {
          imei: parsedData.imei,
          lat: parsedData.latitude,
          lon: parsedData.longitude,
          speed: parsedData.speed
        });

        // Store IMEI
        if (parsedData.imei && !conn.imei) {
          conn.imei = parsedData.imei;
          logger.info(`Device IMEI identified: ${parsedData.imei}`);
        }

        // Add metadata
        parsedData.protocol = conn.protocol;
        parsedData.serverTime = new Date().toISOString();
        parsedData.connectionId = connectionId;

        // Push to Redis queue
        await redisQueue.pushGpsData(parsedData);

        // Generate and send response
        const response = parser.generateResponse(parsedData);
        if (response) {
          conn.socket.write(response);
          logger.debug(`Sent response to ${connectionId}`);
        }

        // Clear buffer after successful parse
        conn.dataBuffer = Buffer.alloc(0);

        // Update device status
        await this.updateDeviceStatus(parsedData.imei, 'online', connectionId);
      } else {
        // Could not parse yet, might need more data
        // Keep buffer but limit size to prevent memory issues
        if (conn.dataBuffer.length > 4096) {
          logger.warn(`Buffer overflow for ${connectionId}, clearing buffer`);
          conn.dataBuffer = Buffer.alloc(0);
        }
      }
    } catch (error) {
      logger.error(`Error processing data from ${connectionId}:`, error);
      conn.dataBuffer = Buffer.alloc(0); // Clear buffer on error
    }
  }

  /**
   * Handle device disconnect
   * @param {string} connectionId - Connection identifier
   */
  async handleDisconnect(connectionId) {
    const conn = this.connections.get(connectionId);
    if (conn) {
      // Update device status to offline
      if (conn.imei) {
        await this.updateDeviceStatus(conn.imei, 'offline', connectionId);
      }

      // Remove connection
      this.connections.delete(connectionId);
    }
  }

  /**
   * Update device online/offline status
   * @param {string} imei - Device IMEI
   * @param {string} status - 'online' or 'offline'
   * @param {string} connectionId - Connection ID
   */
  async updateDeviceStatus(imei, status, connectionId) {
    const statusData = {
      imei,
      status,
      connectionId,
      timestamp: new Date().toISOString(),
    };

    await redisQueue.pushDeviceStatus(statusData);
    logger.info(`Device ${imei} status: ${status}`);
  }

  /**
   * Get connection statistics
   */
  getStats() {
    return {
      totalConnections: this.connections.size,
      servers: Array.from(this.servers.entries()).map(([port, info]) => ({
        port,
        protocol: info.protocol,
      })),
      connections: Array.from(this.connections.entries()).map(([id, conn]) => ({
        id,
        protocol: conn.protocol,
        imei: conn.imei,
        connectedAt: conn.connectedAt,
        lastActivity: conn.lastActivity,
      })),
    };
  }

  /**
   * Stop all servers
   */
  async stop() {
    logger.info('Stopping all TCP servers...');

    // Close all connections
    for (const [id, conn] of this.connections.entries()) {
      conn.socket.end();
    }
    this.connections.clear();

    // Close all servers
    for (const [port, { server }] of this.servers.entries()) {
      await new Promise((resolve) => {
        server.close(() => {
          logger.info(`TCP server on port ${port} stopped`);
          resolve();
        });
      });
    }
    this.servers.clear();

    logger.info('All TCP servers stopped');
  }
}

module.exports = TcpServer;
