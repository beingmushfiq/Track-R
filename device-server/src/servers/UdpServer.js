const dgram = require('dgram');
const logger = require('../utils/logger');
const redisQueue = require('../queue/RedisQueue');

// Import parsers
const GenericParser = require('../parsers/GenericParser');

/**
 * UDP Server for GPS Device Connections
 * Stateless packet handling
 */
class UdpServer {
  constructor() {
    this.servers = new Map();
    this.parsers = new Map();
    
    // Initialize parsers
    this.parsers.set('Generic', new GenericParser());
  }

  /**
   * Start UDP server on specified port
   * @param {number} port - Port number
   * @param {string} protocol - Protocol name
   */
  start(port, protocol = 'Generic') {
    const server = dgram.createSocket('udp4');

    server.on('error', (err) => {
      logger.error(`UDP Server error on port ${port}:`, err);
      server.close();
    });

    server.on('message', async (msg, rinfo) => {
      await this.handleMessage(msg, rinfo, protocol);
    });

    server.on('listening', () => {
      const address = server.address();
      logger.info(`UDP Server listening on ${address.address}:${address.port} for ${protocol} protocol`);
    });

    server.bind(port);

    this.servers.set(port, { server, protocol });
  }

  /**
   * Handle incoming UDP packet
   * @param {Buffer} message - UDP packet data
   * @param {Object} rinfo - Remote info (address, port)
   * @param {string} protocol - Protocol name
   */
  async handleMessage(message, rinfo, protocol) {
    const sourceId = `${rinfo.address}:${rinfo.port}`;
    
    logger.debug(`Received UDP packet from ${sourceId}, size: ${message.length} bytes`);

    const parser = this.parsers.get(protocol);
    if (!parser) {
      logger.error(`No parser found for protocol: ${protocol}`);
      return;
    }

    try {
      const parsedData = parser.parse(message);

      if (parsedData) {
        logger.info(`Parsed UDP data from ${sourceId}:`, {
          imei: parsedData.imei,
          lat: parsedData.latitude,
          lon: parsedData.longitude,
          speed: parsedData.speed
        });

        // Add metadata
        parsedData.protocol = protocol;
        parsedData.serverTime = new Date().toISOString();
        parsedData.sourceAddress = rinfo.address;
        parsedData.sourcePort = rinfo.port;

        // Push to Redis queue
        await redisQueue.pushGpsData(parsedData);

        // UDP is stateless, no response needed typically
        // But some protocols might require acknowledgment
        const response = parser.generateResponse(parsedData);
        if (response) {
          const server = this.servers.get(rinfo.port)?.server;
          if (server) {
            server.send(response, rinfo.port, rinfo.address, (err) => {
              if (err) {
                logger.error(`Failed to send UDP response to ${sourceId}:`, err);
              } else {
                logger.debug(`Sent UDP response to ${sourceId}`);
              }
            });
          }
        }
      } else {
        logger.warn(`Failed to parse UDP packet from ${sourceId}`);
      }
    } catch (error) {
      logger.error(`Error processing UDP packet from ${sourceId}:`, error);
    }
  }

  /**
   * Get server statistics
   */
  getStats() {
    return {
      totalServers: this.servers.size,
      servers: Array.from(this.servers.entries()).map(([port, info]) => ({
        port,
        protocol: info.protocol,
      })),
    };
  }

  /**
   * Stop all UDP servers
   */
  async stop() {
    logger.info('Stopping all UDP servers...');

    for (const [port, { server }] of this.servers.entries()) {
      await new Promise((resolve) => {
        server.close(() => {
          logger.info(`UDP server on port ${port} stopped`);
          resolve();
        });
      });
    }
    this.servers.clear();

    logger.info('All UDP servers stopped');
  }
}

module.exports = UdpServer;
