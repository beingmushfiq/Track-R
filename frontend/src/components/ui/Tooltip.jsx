import React, { useState } from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

const Tooltip = ({ 
  children, 
  content, 
  position = 'top',
  className 
}) => {
  const [isVisible, setIsVisible] = useState(false);

  const positions = {
    top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
    left: 'right-full top-1/2 -translate-y-1/2 mr-2',
    right: 'left-full top-1/2 -translate-y-1/2 ml-2',
  };

  const arrows = {
    top: 'top-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-b-transparent border-t-slate-900 dark:border-t-slate-700',
    bottom: 'bottom-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-t-transparent border-b-slate-900 dark:border-b-slate-700',
    left: 'left-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-r-transparent border-l-slate-900 dark:border-l-slate-700',
    right: 'right-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-l-transparent border-r-slate-900 dark:border-r-slate-700',
  };

  return (
    <div 
      className="relative inline-block"
      onMouseEnter={() => setIsVisible(true)}
      onMouseLeave={() => setIsVisible(false)}
    >
      {children}
      
      {isVisible && content && (
        <div
          className={twMerge(
            clsx(
              "absolute z-50 px-3 py-2 text-sm text-white bg-slate-900 dark:bg-slate-700 rounded-lg shadow-lg whitespace-nowrap animate-in fade-in zoom-in-95 duration-200",
              positions[position],
              className
            )
          )}
        >
          {content}
          {/* Arrow */}
          <div 
            className={twMerge(
              clsx(
                "absolute w-0 h-0 border-4",
                arrows[position]
              )
            )}
          />
        </div>
      )}
    </div>
  );
};

export default Tooltip;
