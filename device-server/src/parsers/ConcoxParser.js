const BaseParser = require('./BaseParser');
const logger = require('../utils/logger');

/**
 * Concox Protocol Parser
 * Used in Concox GPS trackers (GT02, GT06N, etc.)
 * Similar to GT06 but with some variations
 */
class ConcoxParser extends BaseParser {
  constructor() {
    super('Concox');
  }

  parse(buffer) {
    try {
      // Concox uses similar format to GT06
      // Start: 0x7878 or 0x7979
      // We'll reuse GT06 logic with minor adjustments
      
      if (buffer.length < 10) {
        logger.warn('Concox: Packet too small');
        return null;
      }

      const startBits = buffer.readUInt16BE(0);
      if (startBits !== 0x7878 && startBits !== 0x7979) {
        logger.warn('Concox: Invalid start bits');
        return null;
      }

      const packetLength = buffer.readUInt8(2);
      const protocolNumber = buffer.readUInt8(3);

      let parsedData = null;

      switch (protocolNumber) {
        case 0x01: // Login packet
          parsedData = this.parseLogin(buffer);
          break;
        case 0x12: // Location data
        case 0x22: // Location data (extended)
          parsedData = this.parseLocationData(buffer);
          break;
        case 0x13: // Heartbeat
          parsedData = this.parseHeartbeat(buffer);
          break;
        case 0x16: // Alarm
          parsedData = this.parseAlarm(buffer);
          break;
        default:
          logger.debug(`Concox: Unsupported protocol: 0x${protocolNumber.toString(16)}`);
          return null;
      }

      if (parsedData) {
        parsedData.serialNumber = buffer.readUInt16BE(buffer.length - 4);
        parsedData.rawData = this.bufferToHex(buffer);
      }

      return parsedData;
    } catch (error) {
      logger.error('Concox: Parse error:', error);
      return null;
    }
  }

  parseLogin(buffer) {
    const data = {};
    let offset = 4;

    // IMEI (8 bytes BCD)
    const imeiBytes = buffer.slice(offset, offset + 8);
    data.imei = this.bcdToString(imeiBytes);
    offset += 8;

    // Type identifier
    data.typeIdentifier = buffer.readUInt16BE(offset);

    data.messageType = 'login';

    return data;
  }

  parseLocationData(buffer) {
    const data = {};
    let offset = 4;

    // DateTime (6 bytes)
    const year = 2000 + buffer.readUInt8(offset++);
    const month = buffer.readUInt8(offset++);
    const day = buffer.readUInt8(offset++);
    const hour = buffer.readUInt8(offset++);
    const minute = buffer.readUInt8(offset++);
    const second = buffer.readUInt8(offset++);

    data.gpsTime = new Date(year, month - 1, day, hour, minute, second);

    // GPS length and satellites
    const gpsLength = buffer.readUInt8(offset++);
    data.satellites = gpsLength & 0x0F;

    // Latitude (4 bytes)
    const latRaw = buffer.readUInt32BE(offset);
    offset += 4;
    data.latitude = latRaw / 1800000.0;

    // Longitude (4 bytes)
    const lonRaw = buffer.readUInt32BE(offset);
    offset += 4;
    data.longitude = lonRaw / 1800000.0;

    // Speed
    data.speed = buffer.readUInt8(offset++);

    // Course and status
    const courseStatus = buffer.readUInt16BE(offset);
    offset += 2;

    data.heading = courseStatus & 0x03FF;

    // Hemisphere adjustments
    if ((courseStatus & 0x0400) === 0) data.latitude = -data.latitude;
    if ((courseStatus & 0x0800) !== 0) data.longitude = -data.longitude;

    data.gpsPositioned = (courseStatus & 0x1000) !== 0;

    data.messageType = 'location';

    return data;
  }

  parseHeartbeat(buffer) {
    const data = {};
    let offset = 4;

    // Terminal info
    const terminalInfo = buffer.readUInt8(offset++);
    data.batteryLevel = (terminalInfo >> 1) & 0x7F;
    data.ignition = (terminalInfo & 0x01) === 1;

    // Voltage
    const voltage = buffer.readUInt16BE(offset);
    offset += 2;
    data.batteryVoltage = voltage / 100.0;

    // GSM signal
    data.gsmSignal = buffer.readUInt8(offset++);

    data.messageType = 'heartbeat';

    return data;
  }

  parseAlarm(buffer) {
    const data = this.parseLocationData(buffer);
    
    // Alarm type
    const alarmByte = buffer.readUInt8(buffer.length - 6);
    data.alarmType = this.getAlarmType(alarmByte);
    data.messageType = 'alarm';

    return data;
  }

  getAlarmType(byte) {
    const types = {
      0x00: 'normal',
      0x01: 'sos',
      0x02: 'power_cut',
      0x03: 'vibration',
      0x04: 'enter_fence',
      0x05: 'exit_fence',
      0x06: 'overspeed',
    };
    return types[byte] || 'unknown';
  }

  bcdToString(buffer) {
    let result = '';
    for (let i = 0; i < buffer.length; i++) {
      const high = (buffer[i] >> 4) & 0x0F;
      const low = buffer[i] & 0x0F;
      result += high.toString() + low.toString();
    }
    return result;
  }

  generateResponse(parsedData) {
    if (!parsedData || !parsedData.serialNumber) {
      return null;
    }

    const response = Buffer.alloc(10);
    response.writeUInt16BE(0x7878, 0);
    response.writeUInt8(0x05, 2);

    // Protocol number based on message type
    if (parsedData.messageType === 'login') {
      response.writeUInt8(0x01, 3);
    } else if (parsedData.messageType === 'heartbeat') {
      response.writeUInt8(0x13, 3);
    } else {
      response.writeUInt8(0x12, 3);
    }

    response.writeUInt16BE(parsedData.serialNumber, 4);

    // CRC
    let crc = 0;
    for (let i = 2; i < 6; i++) {
      crc += response[i];
    }
    response.writeUInt16BE(crc & 0xFFFF, 6);

    // Stop bits
    response.writeUInt16BE(0x0D0A, 8);

    return response;
  }
}

module.exports = ConcoxParser;
