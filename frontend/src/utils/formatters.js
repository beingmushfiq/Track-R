// Format date to readable string
export const formatDate = (date, includeTime = false) => {
  if (!date) return 'N/A';
  const d = new Date(date);
  const options = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    ...(includeTime && { hour: '2-digit', minute: '2-digit' }),
  };
  return d.toLocaleDateString('en-US', options);
};

// Format relative time (e.g., "2 hours ago")
export const formatRelativeTime = (date) => {
  if (!date) return 'N/A';
  const now = new Date();
  const past = new Date(date);
  const diffMs = now - past;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
  if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
  if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
  return formatDate(date);
};

// Format distance (meters to km)
export const formatDistance = (meters) => {
  if (!meters && meters !== 0) return 'N/A';
  if (meters < 1000) return `${meters.toFixed(0)} m`;
  return `${(meters / 1000).toFixed(2)} km`;
};

// Format speed
export const formatSpeed = (speed) => {
  if (!speed && speed !== 0) return 'N/A';
  return `${speed.toFixed(0)} km/h`;
};

// Format duration (seconds to readable format)
export const formatDuration = (seconds) => {
  if (!seconds && seconds !== 0) return 'N/A';
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  
  if (hours > 0) return `${hours}h ${minutes}m`;
  return `${minutes}m`;
};

// Format number with commas
export const formatNumber = (num) => {
  if (!num && num !== 0) return 'N/A';
  return num.toLocaleString('en-US');
};
