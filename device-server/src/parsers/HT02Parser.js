const BaseParser = require('./BaseParser');
const logger = require('../utils/logger');

/**
 * HT02 Protocol Parser
 * Common in Bangladesh GPS trackers
 * Format: ASCII-based protocol with comma-separated values
 * Example: *HQ,8800000001,V1,121200,A,2234.5678,N,11406.1234,E,000.0,000,010120,FFFFFBFF#
 */
class HT02Parser extends BaseParser {
  constructor() {
    super('HT02');
  }

  parse(buffer) {
    try {
      // Convert buffer to string
      const dataString = buffer.toString('utf8').trim();

      // Check if it starts with *HQ
      if (!dataString.startsWith('*HQ,')) {
        logger.warn('HT02: Invalid packet format');
        return null;
      }

      // Split by comma
      const parts = dataString.split(',');

      if (parts.length < 12) {
        logger.warn('HT02: Insufficient data fields');
        return null;
      }

      const data = {};

      // IMEI (field 1)
      data.imei = parts[1];

      // Command type (field 2)
      const commandType = parts[2];

      // Time (field 3) - HHMMSS
      const timeStr = parts[3];
      const hour = parseInt(timeStr.substring(0, 2));
      const minute = parseInt(timeStr.substring(2, 4));
      const second = parseInt(timeStr.substring(4, 6));

      // GPS Status (field 4) - A=valid, V=invalid
      const gpsStatus = parts[4];
      data.gpsValid = gpsStatus === 'A';

      // Latitude (field 5) - DDMM.MMMM format
      const latStr = parts[5];
      const latDeg = parseInt(latStr.substring(0, 2));
      const latMin = parseFloat(latStr.substring(2));
      data.latitude = latDeg + (latMin / 60);

      // Latitude hemisphere (field 6) - N/S
      if (parts[6] === 'S') {
        data.latitude = -data.latitude;
      }

      // Longitude (field 7) - DDDMM.MMMM format
      const lonStr = parts[7];
      const lonDeg = parseInt(lonStr.substring(0, 3));
      const lonMin = parseFloat(lonStr.substring(3));
      data.longitude = lonDeg + (lonMin / 60);

      // Longitude hemisphere (field 8) - E/W
      if (parts[8] === 'W') {
        data.longitude = -data.longitude;
      }

      // Speed (field 9) - knots, convert to km/h
      const speedKnots = parseFloat(parts[9]);
      data.speed = speedKnots * 1.852;

      // Heading (field 10) - degrees
      data.heading = parseInt(parts[10]);

      // Date (field 11) - DDMMYY
      const dateStr = parts[11];
      const day = parseInt(dateStr.substring(0, 2));
      const month = parseInt(dateStr.substring(2, 4));
      const year = 2000 + parseInt(dateStr.substring(4, 6));

      // Construct GPS time
      data.gpsTime = new Date(year, month - 1, day, hour, minute, second);

      // Status flags (field 12) - hex string
      if (parts.length > 12) {
        const statusHex = parts[12].replace('#', '');
        data.statusFlags = statusHex;
        
        // Parse status flags
        const statusInt = parseInt(statusHex, 16);
        data.ignition = (statusInt & 0x00000001) !== 0;
        data.gpsPositioned = (statusInt & 0x00000002) !== 0;
      }

      data.rawData = dataString;

      return data;
    } catch (error) {
      logger.error('HT02: Parse error:', error);
      return null;
    }
  }

  generateResponse(parsedData) {
    if (!parsedData || !parsedData.imei) {
      return null;
    }

    // HT02 acknowledgment format: *HQ,<IMEI>,V4,<time>,<checksum>#
    const now = new Date();
    const timeStr = now.toISOString().replace(/[-:T]/g, '').substring(8, 14); // HHMMSS
    
    const response = `*HQ,${parsedData.imei},V4,${timeStr},00#`;
    return Buffer.from(response, 'utf8');
  }
}

module.exports = HT02Parser;
