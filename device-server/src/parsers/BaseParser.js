/**
 * Base Parser Interface
 * All protocol parsers must extend this class
 */
class BaseParser {
  constructor(protocolName) {
    this.protocolName = protocolName;
  }

  /**
   * Parse raw data buffer from device
   * @param {Buffer} data - Raw data from device
   * @returns {Object|null} Parsed GPS data or null if invalid
   */
  parse(data) {
    throw new Error('parse() must be implemented by subclass');
  }

  /**
   * Validate parsed data
   * @param {Object} data - Parsed data object
   * @returns {boolean} True if valid
   */
  validate(data) {
    if (!data) return false;
    
    // Basic validation
    return (
      data.imei &&
      typeof data.latitude === 'number' &&
      typeof data.longitude === 'number' &&
      data.latitude >= -90 && data.latitude <= 90 &&
      data.longitude >= -180 && data.longitude <= 180
    );
  }

  /**
   * Extract device info from parsed data
   * @param {Object} data - Parsed data
   * @returns {Object} Device information
   */
  getDeviceInfo(data) {
    return {
      imei: data.imei,
      protocol: this.protocolName,
    };
  }

  /**
   * Extract location from parsed data
   * @param {Object} data - Parsed data
   * @returns {Object} Location information
   */
  extractLocation(data) {
    return {
      latitude: data.latitude,
      longitude: data.longitude,
      altitude: data.altitude || null,
      speed: data.speed || 0,
      heading: data.heading || null,
      satellites: data.satellites || null,
      hdop: data.hdop || null,
    };
  }

  /**
   * Generate response to send back to device
   * @param {Object} parsedData - Parsed data
   * @returns {Buffer|null} Response buffer or null
   */
  generateResponse(parsedData) {
    // Override in subclass if protocol requires response
    return null;
  }

  /**
   * Convert hex string to buffer
   * @param {string} hex - Hex string
   * @returns {Buffer}
   */
  hexToBuffer(hex) {
    return Buffer.from(hex.replace(/\s/g, ''), 'hex');
  }

  /**
   * Convert buffer to hex string
   * @param {Buffer} buffer
   * @returns {string}
   */
  bufferToHex(buffer) {
    return buffer.toString('hex').toUpperCase();
  }

  /**
   * Calculate checksum (XOR)
   * @param {Buffer} buffer
   * @param {number} start
   * @param {number} end
   * @returns {number}
   */
  calculateChecksum(buffer, start, end) {
    let checksum = 0;
    for (let i = start; i < end; i++) {
      checksum ^= buffer[i];
    }
    return checksum;
  }
}

module.exports = BaseParser;
