import React from 'react';

const HealthScoreGauge = ({ score }) => {
  // Determine color based on score
  const getColor = (s) => {
    if (s >= 90) return '#10b981'; // Emerald 500
    if (s >= 75) return '#3b82f6'; // Blue 500
    if (s >= 60) return '#f59e0b'; // Amber 500
    if (s >= 40) return '#f97316'; // Orange 500
    return '#ef4444'; // Red 500
  };

  const color = getColor(score);
  const radius = 50;
  const stroke = 8;
  const normalizedRadius = radius - stroke * 2;
  const circumference = normalizedRadius * 2 * Math.PI;
  const strokeDashoffset = circumference - (score / 100) * circumference;

  return (
    <div className="relative flex items-center justify-center w-32 h-32">
      <svg
        height={radius * 2}
        width={radius * 2}
        className="transform -rotate-90"
      >
        <circle
          stroke="#e5e7eb"
          strokeWidth={stroke}
          fill="transparent"
          r={normalizedRadius}
          cx={radius}
          cy={radius}
        />
        <circle
          stroke={color}
          strokeWidth={stroke}
          strokeDasharray={circumference + ' ' + circumference}
          style={{ strokeDashoffset }}
          strokeLinecap="round"
          fill="transparent"
          r={normalizedRadius}
          cx={radius}
          cy={radius}
        />
      </svg>
      <div className="absolute flex flex-col items-center justify-center">
        <span className="text-2xl font-bold" style={{ color }}>
          {score}
        </span>
        <span className="text-xs text-gray-500 uppercase">Score</span>
      </div>
    </div>
  );
};

export default HealthScoreGauge;
