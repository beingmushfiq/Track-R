const BaseParser = require('./BaseParser');
const logger = require('../utils/logger');

/**
 * Generic Protocol Parser
 * Configurable parser for custom or unknown protocols
 * Uses configuration to define packet structure
 */
class GenericParser extends BaseParser {
  constructor(config = {}) {
    super('Generic');
    this.config = {
      format: config.format || 'ascii', // 'ascii' or 'binary'
      delimiter: config.delimiter || ',',
      startMarker: config.startMarker || null,
      endMarker: config.endMarker || null,
      fields: config.fields || [],
      ...config
    };
  }

  parse(buffer) {
    try {
      if (this.config.format === 'ascii') {
        return this.parseAscii(buffer);
      } else {
        return this.parseBinary(buffer);
      }
    } catch (error) {
      logger.error('Generic: Parse error:', error);
      return null;
    }
  }

  parseAscii(buffer) {
    const dataString = buffer.toString('utf8').trim();

    // Check start marker
    if (this.config.startMarker && !dataString.startsWith(this.config.startMarker)) {
      logger.warn('Generic: Invalid start marker');
      return null;
    }

    // Check end marker
    if (this.config.endMarker && !dataString.endsWith(this.config.endMarker)) {
      logger.warn('Generic: Invalid end marker');
      return null;
    }

    // Remove markers
    let cleanData = dataString;
    if (this.config.startMarker) {
      cleanData = cleanData.substring(this.config.startMarker.length);
    }
    if (this.config.endMarker) {
      cleanData = cleanData.substring(0, cleanData.length - this.config.endMarker.length);
    }

    // Split by delimiter
    const parts = cleanData.split(this.config.delimiter);

    const data = {};

    // Map fields
    this.config.fields.forEach((field, index) => {
      if (index < parts.length) {
        data[field.name] = this.convertValue(parts[index], field.type);
      }
    });

    // Try to extract standard GPS fields
    if (data.lat && data.lon) {
      data.latitude = parseFloat(data.lat);
      data.longitude = parseFloat(data.lon);
    }

    if (data.spd) {
      data.speed = parseFloat(data.spd);
    }

    if (data.dir || data.heading) {
      data.heading = parseInt(data.dir || data.heading);
    }

    data.rawData = dataString;

    return data;
  }

  parseBinary(buffer) {
    const data = {};
    let offset = 0;

    // Parse based on field definitions
    this.config.fields.forEach(field => {
      if (offset >= buffer.length) return;

      switch (field.type) {
        case 'uint8':
          data[field.name] = buffer.readUInt8(offset);
          offset += 1;
          break;
        case 'uint16':
          data[field.name] = buffer.readUInt16BE(offset);
          offset += 2;
          break;
        case 'uint32':
          data[field.name] = buffer.readUInt32BE(offset);
          offset += 4;
          break;
        case 'int8':
          data[field.name] = buffer.readInt8(offset);
          offset += 1;
          break;
        case 'int16':
          data[field.name] = buffer.readInt16BE(offset);
          offset += 2;
          break;
        case 'int32':
          data[field.name] = buffer.readInt32BE(offset);
          offset += 4;
          break;
        case 'float':
          data[field.name] = buffer.readFloatBE(offset);
          offset += 4;
          break;
        case 'double':
          data[field.name] = buffer.readDoubleBE(offset);
          offset += 8;
          break;
        case 'string':
          const length = field.length || 10;
          data[field.name] = buffer.toString('utf8', offset, offset + length).trim();
          offset += length;
          break;
      }
    });

    data.rawData = this.bufferToHex(buffer);

    return data;
  }

  convertValue(value, type) {
    switch (type) {
      case 'int':
      case 'integer':
        return parseInt(value);
      case 'float':
      case 'double':
      case 'number':
        return parseFloat(value);
      case 'bool':
      case 'boolean':
        return value === '1' || value.toLowerCase() === 'true';
      case 'hex':
        return parseInt(value, 16);
      default:
        return value;
    }
  }

  generateResponse(parsedData) {
    // Generic parser doesn't generate responses by default
    // Can be configured if needed
    return null;
  }
}

module.exports = GenericParser;
