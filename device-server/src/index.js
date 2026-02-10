require('dotenv').config();
const logger = require('./utils/logger');
const redisQueue = require('./queue/RedisQueue');
const TcpServer = require('./servers/TcpServer');
const UdpServer = require('./servers/UdpServer');
const HttpServer = require('./servers/HttpServer');

/**
 * Main Application Entry Point
 * Initializes and starts all device servers
 */
class DeviceServerApp {
  constructor() {
    this.tcpServer = new TcpServer();
    this.udpServer = new UdpServer();
    this.httpServer = new HttpServer();
    this.isRunning = false;
  }

  async start() {
    try {
      logger.info('Starting Track-R Device Server...');

      // Connect to Redis
      logger.info('Connecting to Redis...');
      const redisConnected = await redisQueue.connect();
      if (!redisConnected) {
        throw new Error('Failed to connect to Redis');
      }

      // Start TCP servers
      logger.info('Starting TCP servers...');
      this.startTcpServers();

      // Start UDP servers
      logger.info('Starting UDP servers...');
      this.startUdpServers();

      // Start HTTP server
      logger.info('Starting HTTP server...');
      const httpPort = parseInt(process.env.PORT) || 3000;
      this.httpServer.start(httpPort);

      this.isRunning = true;
      logger.info('Track-R Device Server started successfully');

      // Log stats periodically
      this.startStatsLogger();

      // Setup graceful shutdown
      this.setupGracefulShutdown();

    } catch (error) {
      logger.error('Failed to start Device Server:', error);
      process.exit(1);
    }
  }

  startTcpServers() {
    // Start TCP servers for different protocols
    const tcpPorts = {
      HT02: parseInt(process.env.TCP_PORT_HT02) || 5000,
      GT06: parseInt(process.env.TCP_PORT_GT06) || 5001,
      Concox: parseInt(process.env.TCP_PORT_CONCOX) || 5002,
      Generic: parseInt(process.env.TCP_PORT_GENERIC) || 5005,
    };

    for (const [protocol, port] of Object.entries(tcpPorts)) {
      this.tcpServer.start(port, protocol);
    }
  }

  startUdpServers() {
    // Start UDP servers
    const udpPorts = {
      Generic: parseInt(process.env.UDP_PORT_GENERIC) || 6001,
    };

    for (const [protocol, port] of Object.entries(udpPorts)) {
      this.udpServer.start(port, protocol);
    }
  }

  startStatsLogger() {
    // Log statistics every 5 minutes
    setInterval(() => {
      const tcpStats = this.tcpServer.getStats();
      const udpStats = this.udpServer.getStats();
      
      logger.info('Server Statistics:', {
        tcp: {
          connections: tcpStats.totalConnections,
          servers: tcpStats.servers.length,
        },
        udp: {
          servers: udpStats.totalServers,
        },
        memory: process.memoryUsage(),
        uptime: process.uptime(),
      });
    }, 5 * 60 * 1000); // 5 minutes
  }

  setupGracefulShutdown() {
    const shutdown = async (signal) => {
      logger.info(`Received ${signal}, shutting down gracefully...`);

      if (!this.isRunning) {
        return;
      }

      this.isRunning = false;

      try {
        // Stop accepting new connections
        await this.tcpServer.stop();
        await this.udpServer.stop();
        await this.httpServer.stop();

        // Disconnect from Redis
        await redisQueue.disconnect();

        logger.info('Shutdown complete');
        process.exit(0);
      } catch (error) {
        logger.error('Error during shutdown:', error);
        process.exit(1);
      }
    };

    // Handle shutdown signals
    process.on('SIGTERM', () => shutdown('SIGTERM'));
    process.on('SIGINT', () => shutdown('SIGINT'));

    // Handle uncaught exceptions
    process.on('uncaughtException', (error) => {
      logger.error('Uncaught Exception:', error);
      shutdown('uncaughtException');
    });

    // Handle unhandled promise rejections
    process.on('unhandledRejection', (reason, promise) => {
      logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
      shutdown('unhandledRejection');
    });
  }
}

// Start the application
const app = new DeviceServerApp();
app.start();
