import React from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";

/**
 * VehicleActivityTable Component
 * Displays a table of vehicle activity for the day.
 * 
 * @param {Array} vehicles - List of vehicle activity objects
 */
const VehicleActivityTable = ({ vehicles }) => {
  if (!vehicles || vehicles.length === 0) {
    return <div className="text-center p-4 text-muted-foreground">No vehicle activity generated for this date.</div>;
  }

  return (
    <div className="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Vehicle Name</TableHead>
            <TableHead>Status</TableHead>
            <TableHead className="text-right">Distance (km)</TableHead>
            <TableHead className="text-right">Fuel (L)</TableHead>
            <TableHead className="text-right">Duration (hrs)</TableHead>
            <TableHead className="text-right">Safety Events</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {vehicles.map((v) => (
            <TableRow key={v.id}>
              <TableCell className="font-medium">{v.name}</TableCell>
              <TableCell>
                <Badge variant={v.active ? "default" : "secondary"}>
                  {v.active ? 'Active' : 'Idle'}
                </Badge>
              </TableCell>
              <TableCell className="text-right">{v.distance_km}</TableCell>
              <TableCell className="text-right">{v.fuel_liters}</TableCell>
              <TableCell className="text-right">{v.duration_hours}</TableCell>
              <TableCell className="text-right">
                {v.safety_events > 0 ? (
                  <Badge variant="destructive">{v.safety_events}</Badge>
                ) : (
                  <span className="text-muted-foreground">-</span>
                )}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
};

export default VehicleActivityTable;
