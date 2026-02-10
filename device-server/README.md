# Track-R Device Server

GPS Device Ingestion Server for Track-R Platform

## Features

- **Multi-Protocol Support**: HT02, GT06, Concox, Teltonika, Queclink, and Generic protocols
- **Multiple Transport Layers**: TCP, UDP, and HTTP
- **Async Connection Handling**: Supports thousands of concurrent device connections
- **Redis Integration**: Queues GPS data for Laravel backend processing
- **Auto Protocol Detection**: Automatically identifies device protocol
- **Connection Management**: Keep-alive, heartbeat monitoring, timeout handling
- **Comprehensive Logging**: Winston-based structured logging

## Installation

```bash
npm install
```

## Configuration

Copy `.env.example` to `.env` and configure:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

- Redis connection details
- TCP/UDP port assignments
- Logging preferences

## Usage

### Development

```bash
npm run dev
```

### Production

```bash
npm start
```

## Architecture

```
Device → TCP/UDP/HTTP → Parser → Redis Queue → Laravel Backend
```

### Supported Protocols

| Protocol | Transport | Port      | Description                   |
| -------- | --------- | --------- | ----------------------------- |
| HT02     | TCP       | 5000      | Common in Bangladesh trackers |
| GT06     | TCP       | 5001      | Widely used Chinese protocol  |
| Concox   | TCP       | 5002      | Concox GPS trackers           |
| Generic  | TCP/UDP   | 5005/6001 | Configurable custom protocol  |

### HTTP Endpoints

- `POST /api/device/push` - Single GPS data push
- `POST /api/device/push/batch` - Batch GPS data push (max 100 records)
- `GET /health` - Health check
- `GET /api/stats` - Server statistics

## Testing

```bash
npm test
```

## License

MIT
