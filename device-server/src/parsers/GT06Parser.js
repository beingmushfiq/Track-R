const BaseParser = require('./BaseParser');
const logger = require('../utils/logger');

/**
 * GT06 Protocol Parser
 * Common protocol used in many Chinese GPS trackers
 * Format: Start bits (0x7878 or 0x7979) + Length + Protocol Number + Data + Serial + CRC + Stop bits (0x0D0A)
 */
class GT06Parser extends BaseParser {
  constructor() {
    super('GT06');
  }

  parse(buffer) {
    try {
      // Minimum packet size check
      if (buffer.length < 10) {
        logger.warn('GT06: Packet too small');
        return null;
      }

      // Check start bits
      const startBits = buffer.readUInt16BE(0);
      if (startBits !== 0x7878 && startBits !== 0x7979) {
        logger.warn('GT06: Invalid start bits');
        return null;
      }

      // Get packet length
      const packetLength = buffer.readUInt8(2);
      
      // Check if we have complete packet
      if (buffer.length < packetLength + 5) {
        logger.warn('GT06: Incomplete packet');
        return null;
      }

      // Get protocol number
      const protocolNumber = buffer.readUInt8(3);

      // Parse based on protocol number
      let parsedData = null;

      switch (protocolNumber) {
        case 0x12: // Location data
          parsedData = this.parseLocationData(buffer);
          break;
        case 0x13: // Status info
          parsedData = this.parseStatusInfo(buffer);
          break;
        case 0x16: // Alarm data
          parsedData = this.parseAlarmData(buffer);
          break;
        case 0x1A: // GPS + LBS data
          parsedData = this.parseGpsLbsData(buffer);
          break;
        default:
          logger.debug(`GT06: Unsupported protocol number: 0x${protocolNumber.toString(16)}`);
          return null;
      }

      if (parsedData) {
        // Add serial number
        const serialNumber = buffer.readUInt16BE(buffer.length - 4);
        parsedData.serialNumber = serialNumber;
        parsedData.rawData = this.bufferToHex(buffer);
      }

      return parsedData;
    } catch (error) {
      logger.error('GT06: Parse error:', error);
      return null;
    }
  }

  parseLocationData(buffer) {
    const data = {};
    let offset = 4; // Start after protocol number

    // Date and time (6 bytes)
    const year = 2000 + buffer.readUInt8(offset++);
    const month = buffer.readUInt8(offset++);
    const day = buffer.readUInt8(offset++);
    const hour = buffer.readUInt8(offset++);
    const minute = buffer.readUInt8(offset++);
    const second = buffer.readUInt8(offset++);

    data.gpsTime = new Date(year, month - 1, day, hour, minute, second);

    // GPS info length and satellites
    const gpsInfoLength = buffer.readUInt8(offset++);
    data.satellites = gpsInfoLength & 0x0F;

    // Latitude (4 bytes)
    const latitudeRaw = buffer.readUInt32BE(offset);
    offset += 4;
    data.latitude = latitudeRaw / 1800000.0;

    // Longitude (4 bytes)
    const longitudeRaw = buffer.readUInt32BE(offset);
    offset += 4;
    data.longitude = longitudeRaw / 1800000.0;

    // Speed (1 byte, km/h)
    data.speed = buffer.readUInt8(offset++);

    // Course/Status (2 bytes)
    const courseStatus = buffer.readUInt16BE(offset);
    offset += 2;
    
    data.heading = courseStatus & 0x03FF;
    
    // Check GPS positioning
    const gpsPositioned = (courseStatus & 0x1000) !== 0;
    
    // Adjust lat/lng based on hemisphere bits
    if ((courseStatus & 0x0400) === 0) data.latitude = -data.latitude;  // South
    if ((courseStatus & 0x0800) !== 0) data.longitude = -data.longitude; // West

    if (!gpsPositioned) {
      logger.debug('GT06: GPS not positioned');
    }

    // MCC, MNC, LAC, Cell ID (LBS data) - skip for now
    // offset += 9;

    return data;
  }

  parseStatusInfo(buffer) {
    // Terminal info packet
    const data = {};
    let offset = 4;

    // Terminal info byte
    const terminalInfo = buffer.readUInt8(offset++);
    data.batteryLevel = (terminalInfo >> 1) & 0x7F; // Battery percentage
    data.ignition = (terminalInfo & 0x01) === 1;

    // Voltage level
    const voltageLevel = buffer.readUInt16BE(offset);
    offset += 2;
    data.batteryVoltage = voltageLevel / 100.0; // Convert to volts

    // GSM signal strength
    data.gsmSignal = buffer.readUInt8(offset++);

    // Alarm/Language
    const alarmLang = buffer.readUInt16BE(offset);
    offset += 2;
    data.alarm = (alarmLang >> 8) & 0xFF;

    return data;
  }

  parseAlarmData(buffer) {
    // Similar to location data but with alarm info
    const data = this.parseLocationData(buffer);
    
    // Add alarm type
    const alarmType = buffer.readUInt8(buffer.length - 6);
    data.alarmType = this.getAlarmType(alarmType);

    return data;
  }

  parseGpsLbsData(buffer) {
    // Extended location data with LBS
    return this.parseLocationData(buffer);
  }

  getAlarmType(alarmByte) {
    const alarmTypes = {
      0x00: 'normal',
      0x01: 'sos',
      0x02: 'power_cut',
      0x03: 'vibration',
      0x04: 'enter_fence',
      0x05: 'exit_fence',
      0x06: 'overspeed',
      0x09: 'moving',
    };
    return alarmTypes[alarmByte] || 'unknown';
  }

  generateResponse(parsedData) {
    if (!parsedData || !parsedData.serialNumber) {
      return null;
    }

    // Generate acknowledgment response
    const response = Buffer.alloc(10);
    response.writeUInt16BE(0x7878, 0); // Start bits
    response.writeUInt8(0x05, 2);      // Length
    response.writeUInt8(0x12, 3);      // Protocol number (location)
    response.writeUInt16BE(parsedData.serialNumber, 4); // Serial number
    
    // Calculate CRC
    const crc = this.calculateCRC(response, 2, 6);
    response.writeUInt16BE(crc, 6);
    
    // Stop bits
    response.writeUInt16BE(0x0D0A, 8);

    return response;
  }

  calculateCRC(buffer, start, end) {
    let crc = 0;
    for (let i = start; i < end; i++) {
      crc += buffer[i];
    }
    return crc & 0xFFFF;
  }
}

module.exports = GT06Parser;
