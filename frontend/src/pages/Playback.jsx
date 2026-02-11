import { useState } from 'react';
import { Calendar, Play, Pause, SkipBack, SkipForward, Clock, MapPin, TrendingUp } from 'lucide-react';
import { MapContainer, TileLayer, Polyline, Marker, Popup } from 'react-leaflet';
import { Card, Button, Select, Spinner } from '../components/ui';
import { formatDate, formatDistance, formatSpeed, formatDuration } from '../utils/formatters';
import 'leaflet/dist/leaflet.css';

const Playback = () => {
  const [isPlaying, setIsPlaying] = useState(false);
  const [playbackSpeed, setPlaybackSpeed] = useState('1');
  const [currentPosition, setCurrentPosition] = useState(0);
  const [selectedTrip, setSelectedTrip] = useState(null);

  // Mock trip data
  const trips = [
    {
      id: 1,
      vehicle: 'Truck 001',
      startTime: '2024-02-11 08:00:00',
      endTime: '2024-02-11 12:30:00',
      distance: 145.5,
      duration: 16200, // seconds
      maxSpeed: 85,
      avgSpeed: 45,
    },
    {
      id: 2,
      vehicle: 'Truck 001',
      startTime: '2024-02-11 14:00:00',
      endTime: '2024-02-11 18:15:00',
      distance: 178.2,
      duration: 15300,
      maxSpeed: 92,
      avgSpeed: 52,
    },
  ];

  // Mock route path
  const routePath = [
    [23.8103, 90.4125], // Dhaka
    [23.8150, 90.4200],
    [23.8200, 90.4300],
    [23.8250, 90.4400],
    [23.8300, 90.4500],
    [23.8350, 90.4600],
    [23.8400, 90.4700],
  ];

  const speedOptions = [
    { value: '0.5', label: '0.5x' },
    { value: '1', label: '1x' },
    { value: '2', label: '2x' },
    { value: '5', label: '5x' },
    { value: '10', label: '10x' },
  ];

  const handlePlayPause = () => {
    setIsPlaying(!isPlaying);
    // TODO: Implement actual playback logic
  };

  const handleReset = () => {
    setCurrentPosition(0);
    setIsPlaying(false);
  };

  const handlePositionChange = (e) => {
    setCurrentPosition(parseInt(e.target.value));
  };

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Trip Playback</h1>
          <p className="text-slate-500 dark:text-slate-400">Replay and analyze trip routes</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Trip List */}
        <div className="lg:col-span-1">
          <Card>
            <Card.Header>
              <h3 className="text-lg font-semibold text-slate-900 dark:text-white">Recent Trips</h3>
            </Card.Header>
            <Card.Body>
              <div className="space-y-3">
                {trips.map((trip) => (
                  <button
                    key={trip.id}
                    onClick={() => setSelectedTrip(trip)}
                    className={`w-full p-4 rounded-lg border transition-all text-left ${
                      selectedTrip?.id === trip.id
                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10'
                        : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
                    }`}
                  >
                    <div className="flex items-center justify-between mb-2">
                      <span className="font-semibold text-slate-900 dark:text-white">{trip.vehicle}</span>
                      <span className="text-sm text-slate-500 dark:text-slate-400">
                        {formatDate(trip.startTime)}
                      </span>
                    </div>
                    <div className="grid grid-cols-2 gap-2 text-sm">
                      <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                        <MapPin className="w-4 h-4" />
                        <span>{formatDistance(trip.distance * 1000)}</span>
                      </div>
                      <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                        <Clock className="w-4 h-4" />
                        <span>{formatDuration(trip.duration)}</span>
                      </div>
                      <div className="flex items-center gap-1 text-slate-600 dark:text-slate-400">
                        <TrendingUp className="w-4 h-4" />
                        <span>{formatSpeed(trip.avgSpeed)}</span>
                      </div>
                    </div>
                  </button>
                ))}
              </div>
            </Card.Body>
          </Card>
        </div>

        {/* Map & Playback Controls */}
        <div className="lg:col-span-2 space-y-4">
          {/* Map */}
          <Card>
            <Card.Body className="p-0">
              <div className="h-[500px] rounded-2xl overflow-hidden">
                {selectedTrip ? (
                  <MapContainer
                    center={routePath[currentPosition] || routePath[0]}
                    zoom={13}
                    style={{ height: '100%', width: '100%' }}
                  >
                    <TileLayer
                      url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                      attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    />
                    
                    {/* Route path */}
                    <Polyline
                      positions={routePath.slice(0, currentPosition + 1)}
                      color="#6366f1"
                      weight={4}
                      opacity={0.7}
                    />

                    {/* Current position marker */}
                    {routePath[currentPosition] && (
                      <Marker position={routePath[currentPosition]}>
                        <Popup>
                          <div className="text-sm">
                            <p className="font-semibold">{selectedTrip.vehicle}</p>
                            <p>Position: {currentPosition + 1}/{routePath.length}</p>
                          </div>
                        </Popup>
                      </Marker>
                    )}
                  </MapContainer>
                ) : (
                  <div className="h-full flex items-center justify-center bg-slate-100 dark:bg-slate-800">
                    <p className="text-slate-500 dark:text-slate-400">Select a trip to view playback</p>
                  </div>
                )}
              </div>
            </Card.Body>
          </Card>

          {/* Playback Controls */}
          {selectedTrip && (
            <Card>
              <Card.Body>
                <div className="space-y-4">
                  {/* Timeline Slider */}
                  <div>
                    <input
                      type="range"
                      min="0"
                      max={routePath.length - 1}
                      value={currentPosition}
                      onChange={handlePositionChange}
                      className="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                    />
                    <div className="flex justify-between text-sm text-slate-500 dark:text-slate-400 mt-2">
                      <span>{formatDate(selectedTrip.startTime, true)}</span>
                      <span>Position: {currentPosition + 1}/{routePath.length}</span>
                      <span>{formatDate(selectedTrip.endTime, true)}</span>
                    </div>
                  </div>

                  {/* Control Buttons */}
                  <div className="flex items-center justify-center gap-4">
                    <Button variant="secondary" size="sm" onClick={handleReset}>
                      <SkipBack className="w-4 h-4" />
                    </Button>
                    
                    <Button onClick={handlePlayPause} className="px-8">
                      {isPlaying ? (
                        <>
                          <Pause className="w-5 h-5 mr-2" />
                          Pause
                        </>
                      ) : (
                        <>
                          <Play className="w-5 h-5 mr-2" />
                          Play
                        </>
                      )}
                    </Button>

                    <Button variant="secondary" size="sm">
                      <SkipForward className="w-4 h-4" />
                    </Button>

                    <div className="ml-4">
                      <Select
                        options={speedOptions}
                        value={playbackSpeed}
                        onChange={setPlaybackSpeed}
                        className="w-24"
                      />
                    </div>
                  </div>

                  {/* Trip Stats */}
                  <div className="grid grid-cols-4 gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <div className="text-center">
                      <p className="text-sm text-slate-500 dark:text-slate-400">Distance</p>
                      <p className="text-lg font-semibold text-slate-900 dark:text-white">
                        {formatDistance(selectedTrip.distance * 1000)}
                      </p>
                    </div>
                    <div className="text-center">
                      <p className="text-sm text-slate-500 dark:text-slate-400">Duration</p>
                      <p className="text-lg font-semibold text-slate-900 dark:text-white">
                        {formatDuration(selectedTrip.duration)}
                      </p>
                    </div>
                    <div className="text-center">
                      <p className="text-sm text-slate-500 dark:text-slate-400">Avg Speed</p>
                      <p className="text-lg font-semibold text-slate-900 dark:text-white">
                        {formatSpeed(selectedTrip.avgSpeed)}
                      </p>
                    </div>
                    <div className="text-center">
                      <p className="text-sm text-slate-500 dark:text-slate-400">Max Speed</p>
                      <p className="text-lg font-semibold text-slate-900 dark:text-white">
                        {formatSpeed(selectedTrip.maxSpeed)}
                      </p>
                    </div>
                  </div>
                </div>
              </Card.Body>
            </Card>
          )}
        </div>
      </div>
    </div>
  );
};

export default Playback;
