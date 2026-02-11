import { useState, useEffect } from 'react';
import { AlertCircle, CheckCircle, MapPin, Clock, User } from 'lucide-react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import api from '../services/api';

export default function PanicEventsDashboard() {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('active'); // active, resolved, all
  const [selectedEvent, setSelectedEvent] = useState(null);

  useEffect(() => {
    fetchEvents();
  }, [filter]);

  const fetchEvents = async () => {
    setLoading(true);
    try {
      const response = await api.get('/api/panic-events', {
        params: { status: filter }
      });
      setEvents(response.data.data || response.data);
    } catch (error) {
      console.error('Failed to fetch panic events:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleResolve = async (eventId, notes) => {
    try {
      await api.post(`/api/panic-events/${eventId}/resolve`, { notes });
      fetchEvents();
      setSelectedEvent(null);
    } catch (error) {
      console.error('Failed to resolve panic event:', error);
      alert('Failed to resolve panic event');
    }
  };

  const ResolveDialog = ({ event }) => {
    const [notes, setNotes] = useState('');
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async () => {
      setSubmitting(true);
      await handleResolve(event.id, notes);
      setSubmitting(false);
    };

    return (
      <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 className="text-lg font-semibold mb-4">Resolve Panic Event</h3>
          
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Resolution Notes
            </label>
            <textarea
              value={notes}
              onChange={(e) => setNotes(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              rows="4"
              placeholder="Describe how the panic event was resolved..."
            />
          </div>

          <div className="flex gap-3 justify-end">
            <button
              onClick={() => setSelectedEvent(null)}
              className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              disabled={submitting}
            >
              Cancel
            </button>
            <button
              onClick={handleSubmit}
              className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
              disabled={submitting}
            >
              {submitting ? 'Resolving...' : 'Resolve Event'}
            </button>
          </div>
        </div>
      </div>
    );
  };

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold mb-2">Panic Events Dashboard</h1>
        <p className="text-gray-600">Monitor and respond to emergency alerts</p>
      </div>

      {/* Filter Tabs */}
      <div className="flex gap-2 mb-6">
        {['active', 'resolved', 'all'].map((status) => (
          <button
            key={status}
            onClick={() => setFilter(status)}
            className={`px-4 py-2 rounded-lg font-medium transition-colors ${
              filter === status
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            {status.charAt(0).toUpperCase() + status.slice(1)}
          </button>
        ))}
      </div>

      {/* Events List */}
      {loading ? (
        <div className="text-center py-12">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="text-gray-600 mt-4">Loading panic events...</p>
        </div>
      ) : events.length === 0 ? (
        <div className="text-center py-12 bg-gray-50 rounded-lg">
          <AlertCircle className="mx-auto text-gray-400 mb-4" size={48} />
          <p className="text-gray-600">No panic events found</p>
        </div>
      ) : (
        <div className="grid gap-4">
          {events.map((event) => (
            <div
              key={event.id}
              className={`bg-white rounded-lg shadow p-4 border-l-4 ${
                event.resolved_at ? 'border-green-500' : 'border-red-500'
              }`}
            >
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2">
                    {event.resolved_at ? (
                      <CheckCircle className="text-green-600" size={20} />
                    ) : (
                      <AlertCircle className="text-red-600" size={20} />
                    )}
                    <h3 className="font-semibold">
                      {event.vehicle?.name || `Device ${event.device?.imei}`}
                    </h3>
                    <span
                      className={`px-2 py-1 rounded text-xs font-medium ${
                        event.resolved_at
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      }`}
                    >
                      {event.resolved_at ? 'Resolved' : 'Active'}
                    </span>
                  </div>

                  <div className="grid grid-cols-2 gap-4 text-sm text-gray-600">
                    <div className="flex items-center gap-2">
                      <Clock size={14} />
                      <span>
                        Triggered: {new Date(event.triggered_at).toLocaleString()}
                      </span>
                    </div>
                    
                    <div className="flex items-center gap-2">
                      <MapPin size={14} />
                      <span>
                        {event.latitude.toFixed(6)}, {event.longitude.toFixed(6)}
                      </span>
                    </div>

                    {event.resolved_at && (
                      <>
                        <div className="flex items-center gap-2">
                          <CheckCircle size={14} />
                          <span>
                            Resolved: {new Date(event.resolved_at).toLocaleString()}
                          </span>
                        </div>
                        
                        {event.resolved_by && (
                          <div className="flex items-center gap-2">
                            <User size={14} />
                            <span>By: {event.resolved_by}</span>
                          </div>
                        )}
                      </>
                    )}
                  </div>

                  {event.notes && (
                    <div className="mt-2 p-2 bg-gray-50 rounded text-sm text-gray-700">
                      <strong>Notes:</strong> {event.notes}
                    </div>
                  )}
                </div>

                {!event.resolved_at && (
                  <button
                    onClick={() => setSelectedEvent(event)}
                    className="ml-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium"
                  >
                    Resolve
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedEvent && <ResolveDialog event={selectedEvent} />}
    </div>
  );
}
