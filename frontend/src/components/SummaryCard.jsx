import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Users, AlertTriangle, Truck, Fuel, Clock, Activity } from "lucide-react";

/**
 * SummaryCard Component
 * Displays a key metric with an icon and label.
 * 
 * @param {string} title - The title of the card
 * @param {string|number} value - The main value to display
 * @param {string} subtitle - Optional subtitle or unit
 * @param {React.ReactNode} icon - Icon component
 * @param {string} trend - Optional trend indicator (e.g., "+5%")
 */
const SummaryCard = ({ title, value, subtitle, icon, trend, className }) => {
  return (
    <Card className={`hover:shadow-md transition-shadow ${className}`}>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium text-muted-foreground">
          {title}
        </CardTitle>
        {icon && <div className="h-4 w-4 text-muted-foreground">{icon}</div>}
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{value}</div>
        {(subtitle || trend) && (
          <p className="text-xs text-muted-foreground mt-1">
            {trend && <span className={trend.startsWith('+') ? "text-green-500 mr-1" : "text-red-500 mr-1"}>{trend}</span>}
            {subtitle}
          </p>
        )}
      </CardContent>
    </Card>
  );
};

export default SummaryCard;
